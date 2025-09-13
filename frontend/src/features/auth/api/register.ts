import { api } from '../../../shared/api'

export type RegisterPayload = {
  name: string
  email: string
  password: string
  password_confirmation: string
}

export type RegisterResponse = {
  data: { id: number; name: string; email: string }
}

export async function register(payload: RegisterPayload): Promise<RegisterResponse> {
  const { data } = await api.post<RegisterResponse>('/api/auth/register', payload)
  return data
}

