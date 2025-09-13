import { api } from '../../../shared/api'

export type CepData = {
  cep: string
  logradouro?: string
  bairro?: string
  localidade?: string
  uf?: string
}

export type CepResponse = { data: CepData }

export async function fetchCep(cep: string): Promise<CepData> {
  const normalized = cep.replace(/\D+/g, '')
  const { data } = await api.get<CepResponse>(`/api/address/cep/${normalized}`)
  return data.data
}

