import { useQuery } from '@tanstack/react-query'
import { fetchContacts, type SearchParams } from '../api/search'

export type GeoParams = Required<Pick<SearchParams, 'page' | 'per_page' | 'sort' | 'order'>> & {
  q?: string
}

export function useGeoSearch(params: GeoParams) {
  return useQuery({
    queryKey: ['contacts.geo', params],
    queryFn: ({ signal }) => fetchContacts({ ...params, has_geo: true }, { signal }),
    staleTime: 15_000,
    placeholderData: (prev) => prev,
    refetchOnWindowFocus: false,
  })
}

