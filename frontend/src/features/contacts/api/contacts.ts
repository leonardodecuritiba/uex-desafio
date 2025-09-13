import { api } from '../../../shared/api'
import { csrf } from '../../auth/api/auth'

export type AddressPayload = {
  cep?: string
  logradouro?: string
  numero?: string
  complemento?: string | null
  bairro?: string
  localidade?: string
  uf?: string
  lat?: number | null
  lng?: number | null
}

export type ContactPayload = {
  name: string
  cpf?: string | null
  email?: string | null
  phone?: string | null
  address?: AddressPayload | null
}

export type ContactResponse = {
  data: {
    id: number
    name: string
    cpf?: string | null
    email?: string | null
    phone?: string | null
    address?: AddressPayload | null
    created_at?: string
    updated_at?: string
  }
}

export async function createContact(payload: ContactPayload): Promise<ContactResponse> {
  await csrf()
  const { data } = await api.post<ContactResponse>('/api/contacts', payload)
  return data
}

export async function getContact(id: number): Promise<ContactResponse> {
  const { data } = await api.get<ContactResponse>(`/api/contacts/${id}`)
  return data
}

export async function updateContact(
  id: number,
  patch: Partial<ContactPayload> & { version?: string }
): Promise<ContactResponse> {
  await csrf()
  const { data } = await api.patch<ContactResponse>(`/api/contacts/${id}`, patch)
  return data
}

export async function deleteContact(id: number): Promise<void> {
  await csrf()
  await api.delete(`/api/contacts/${id}`)
}
