import { api } from '../../../shared/api'

export type DeleteAccountPayload = { password: string }
export type DeleteAccountResponse = { message: string }

async function csrf(): Promise<void> {
  await api.get('/sanctum/csrf-cookie')
}

export async function deleteAccount(payload: DeleteAccountPayload): Promise<DeleteAccountResponse> {
  await csrf()
  const { data } = await api.delete<DeleteAccountResponse>('/api/account', { data: payload })
  return data
}

