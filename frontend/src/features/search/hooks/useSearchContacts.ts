import { useQuery } from '@tanstack/react-query'
import { fetchContacts, type SearchParams } from '../api/search'
import type { SearchResponse } from '../types/contact'

export function useSearchContacts(params: SearchParams) {
  return useQuery<SearchResponse>({
    queryKey: ['contacts.search', params],
    queryFn: ({ signal }) => fetchContacts(params, { signal }),
    keepPreviousData: true,
    staleTime: 15_000,
  })
}

