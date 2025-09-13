import { useState } from 'react'
import { fetchCep, type CepData } from '../api/address'

export function useCepLookup() {
  const [loading, setLoading] = useState(false)
  const [error, setError] = useState<string | null>(null)

  async function lookup(cep: string): Promise<CepData | null> {
    setLoading(true)
    setError(null)
    try {
      const data = await fetchCep(cep)
      return data
    // eslint-disable-next-line @typescript-eslint/no-unused-vars
    } catch (e) {
      setError('CEP não encontrado.')
      return null
    } finally {
      setLoading(false)
    }
  }

  return { lookup, loading, error, setError }
}

