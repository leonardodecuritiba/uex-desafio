import { useMutation } from '@tanstack/react-query'
import { createContact, type ContactPayload, type ContactResponse } from '../api/contacts'

type BackendError = { errors?: { field: string; message: string }[]; message?: string }

export function useCreateContact() {
  return useMutation<ContactResponse, BackendError, ContactPayload>({
    mutationFn: (payload) => createContact(payload),
  })
}

