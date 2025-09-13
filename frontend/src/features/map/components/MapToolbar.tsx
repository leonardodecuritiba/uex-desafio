import { useEffect, useMemo, useRef, useState } from 'react'

type Props = {
  q: string
  sort: 'created_at' | 'name'
  order: 'asc' | 'desc'
  total?: number
  canLoadMore: boolean
  loading: boolean
  onChange: (params: Partial<{ q: string; sort: 'created_at' | 'name'; order: 'asc' | 'desc' }>) => void
  onLoadMore: () => void
}

export default function MapToolbar({ q, sort, order, total, canLoadMore, loading, onChange, onLoadMore }: Props) {
  const [localQ, setLocalQ] = useState(q)
  const timeoutRef = useRef<number | null>(null)

  useEffect(() => {
    setLocalQ(q)
  }, [q])

  useEffect(() => {
    if (timeoutRef.current) window.clearTimeout(timeoutRef.current)
    timeoutRef.current = window.setTimeout(() => {
      if (localQ !== q) onChange({ q: localQ })
    }, 400)
    return () => {
      if (timeoutRef.current) window.clearTimeout(timeoutRef.current)
    }
  }, [localQ])

  const totalLabel = useMemo(() => {
    if (typeof total !== 'number') return '–'
    return String(total)
  }, [total])

  return (
    <div style={{ display: 'flex', alignItems: 'center', gap: 12, padding: 8 }}>
      <label>
        <span className="sr-only">buscar</span>
        <input
          aria-label="buscar"
          placeholder="Buscar por nome ou CPF"
          value={localQ}
          onChange={(e) => setLocalQ(e.target.value)}
          style={{ padding: 8, minWidth: 260 }}
        />
      </label>

      <label>
        <span className="sr-only">ordenar por</span>
        <select aria-label="ordenar por" value={sort} onChange={(e) => onChange({ sort: e.target.value as any })}>
          <option value="created_at">Criação</option>
          <option value="name">Nome</option>
        </select>
      </label>

      <label>
        <span className="sr-only">ordem</span>
        <select aria-label="ordem" value={order} onChange={(e) => onChange({ order: e.target.value as any })}>
          <option value="desc">Desc</option>
          <option value="asc">Asc</option>
        </select>
      </label>

      <span aria-live="polite">Total: {totalLabel}</span>

      <button onClick={onLoadMore} disabled={!canLoadMore || loading} aria-label="carregar mais">
        Carregar mais
      </button>
      {loading && <span role="status" aria-live="polite">Carregando...</span>}
    </div>
  )
}

