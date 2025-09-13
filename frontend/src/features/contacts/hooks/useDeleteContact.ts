/* eslint-disable @typescript-eslint/no-explicit-any */
import { useMutation, useQueryClient } from '@tanstack/react-query'
import { deleteContact } from '../api/contacts'

type BackendError = { status?: number; errors?: { field: string; message: string }[]; message?: string }

export function useDeleteContact() {
  const qc = useQueryClient()
  return useMutation<boolean, BackendError, { id: number}>({
    mutationFn: async ({ id }) => {
      await deleteContact(id)
      return true
    },
    onSuccess: (_ok, { id }) => {
      // Remove do cache de lista, se existir
      qc.setQueryData<any>(['contacts'], (prev) => {
        if (!prev) return prev
        if (Array.isArray(prev)) return prev.filter((c) => c.id !== id)
        if (prev?.items) return { ...prev, items: prev.items.filter((c: any) => c.id !== id) }
        return prev
      })
      // Remove cache do detalhe
      qc.removeQueries({ queryKey: ['contact', id] })
    },
  })
}

