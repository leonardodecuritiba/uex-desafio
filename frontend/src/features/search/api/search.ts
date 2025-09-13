import { api } from '../../../shared/api'
import type { SearchResponse } from '../types/contact'

export type SearchParams = {
  q?: string
  has_geo?: boolean
  page?: number
  per_page?: number
  sort?: 'created_at' | 'name'
  order?: 'asc' | 'desc'
}

export async function fetchContacts(
  params: SearchParams,
  opts?: { signal?: AbortSignal }
): Promise<SearchResponse> {
  const { data } = await api.get<SearchResponse>('/api/contacts', {
    params: {
      q: params.q || undefined,
      has_geo:
        typeof params.has_geo === 'boolean' ? String(params.has_geo) : undefined,
      page: params.page,
      per_page: params.per_page,
      sort: params.sort,
      order: params.order,
    },
    signal: opts?.signal,
    withCredentials: true,
  })
  return data
}

