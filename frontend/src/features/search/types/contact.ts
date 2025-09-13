export type ContactAddress = {
  cep?: string | null
  logradouro?: string | null
  numero?: string | null
  complemento?: string | null
  bairro?: string | null
  localidade?: string | null
  uf?: string | null
  lat?: number | null
  lng?: number | null
}

export type ContactItem = {
  id: number
  name: string
  cpf?: string | null
  email?: string | null
  phone?: string | null
  address?: ContactAddress | null
}

export type SearchResponse = {
  data: ContactItem[]
  meta: {
    page: number
    per_page: number
    total: number
    last_page: number
    sort?: 'created_at' | 'name'
    order?: 'asc' | 'desc'
  }
}

