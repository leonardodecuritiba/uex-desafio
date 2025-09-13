import { useMutation, useQueryClient } from '@tanstack/react-query'
import { updateContact, type ContactPayload, type ContactResponse } from '../api/contacts'

type BackendError = { errors?: { field: string; message: string }[]; message?: string }

export function useEditContact(id: number) {
  const qc = useQueryClient()
  return useMutation<ContactResponse, BackendError, Partial<ContactPayload> & { version?: string }>({
    mutationFn: (payload) => updateContact(id, payload),
    onSuccess: (data) => {
      qc.setQueryData(['contact', id], data)
      qc.invalidateQueries({ queryKey: ['contacts'] })
    },
  })
}

