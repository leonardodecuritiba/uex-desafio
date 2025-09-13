import { api } from '../../../shared/api'

export async function csrf(): Promise<void> {
  // Realiza o handshake CSRF (Sanctum)
  await api.get('/sanctum/csrf-cookie')
}

export type LoginPayload = { email: string; password: string }
export type LoginResponse = { data: { id: number; name: string; email: string } }
export async function login(payload: LoginPayload): Promise<LoginResponse> {
  await csrf()
  const { data } = await api.post<LoginResponse>('/api/auth/login', payload)
  return data
}

export type MeResponse = { data: { id: number; name: string; email: string } }
export async function me(): Promise<MeResponse> {
  const { data } = await api.get<MeResponse>('/api/auth/me')
  return data
}

export type LogoutResponse = { message: string }
export async function logout(): Promise<LogoutResponse> {
  await csrf()
  const { data } = await api.post<LogoutResponse>('/api/auth/logout')
  return data
}

export type ForgotPasswordPayload = { email: string }
export type ForgotPasswordResponse = { message: string }

export async function forgotPassword(payload: ForgotPasswordPayload): Promise<ForgotPasswordResponse> {
  const { data } = await api.post<ForgotPasswordResponse>('/api/auth/forgot-password', payload)
  return data
}

export type ResetPasswordPayload = {
  token: string
  email: string
  password: string
  password_confirmation: string
}
export type ResetPasswordResponse = { message: string }

export async function resetPassword(payload: ResetPasswordPayload): Promise<ResetPasswordResponse> {
  const { data } = await api.post<ResetPasswordResponse>('/api/auth/reset-password', payload)
  return data
}
