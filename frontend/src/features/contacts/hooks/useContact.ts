import { useQuery } from '@tanstack/react-query'
import { getContact } from '../api/contacts'

export function useContact(id: number | undefined) {
  return useQuery({
    queryKey: ['contact', id],
    queryFn: () => getContact(id as number),
    enabled: !!id,
  })
}

