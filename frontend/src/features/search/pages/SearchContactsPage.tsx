import { useMemo } from 'react'
import { useSearchParams } from 'react-router-dom'
import { useSearchContacts } from '../hooks/useSearchContacts'
import SearchBar from '../components/SearchBar'
import SearchFilters from '../components/SearchFilters'
import ContactList from '../components/ContactList'
import MapCanvas from '../../map/components/MapCanvas'
import { useSelectionSync } from '../hooks/useSelectionSync'

export default function SearchContactsPage() {
  const [params, setParams] = useSearchParams()
  const q = params.get('q') || ''
  const hasGeoParam = params.get('has_geo')
  const hasGeo: 'all' | 'true' | 'false' = hasGeoParam === 'true' ? 'true' : hasGeoParam === 'false' ? 'false' : 'all'
  const page = Number(params.get('page') || 1)
  const perPage = Number(params.get('per_page') || 20)
  const sort = (params.get('sort') || 'created_at') as 'created_at' | 'name'
  const order = (params.get('order') || 'desc') as 'asc' | 'desc'

  const reqParams = useMemo(
    () => ({ q: q || undefined, has_geo: hasGeo === 'all' ? undefined : hasGeo === 'true', page, per_page: perPage, sort, order }),
    [q, hasGeo, page, perPage, sort, order]
  )
  const { data, isLoading, isError, refetch } = useSearchContacts(reqParams)
  const { selectedId, onSelect } = useSelectionSync()

  function update(next: Partial<{ q: string; hasGeo: 'all' | 'true' | 'false'; page: number; perPage: number; sort: 'created_at' | 'name'; order: 'asc' | 'desc' }>) {
    const n = new URLSearchParams(params)
    if (next.q !== undefined) n.set('q', next.q)
    if (next.hasGeo !== undefined) {
      if (next.hasGeo === 'all') n.delete('has_geo')
      else n.set('has_geo', next.hasGeo)
    }
    if (next.page !== undefined) n.set('page', String(next.page))
    if (next.perPage !== undefined) n.set('per_page', String(next.perPage))
    if (next.sort !== undefined) n.set('sort', next.sort)
    if (next.order !== undefined) n.set('order', next.order)
    // reset page ao alterar filtros ou q
    if (next.q !== undefined || next.hasGeo !== undefined || next.sort !== undefined || next.order !== undefined || next.perPage !== undefined) {
      n.set('page', '1')
    }
    setParams(n)
  }

  return (
    <div style={{ display: 'grid', gridTemplateColumns: '1fr 1fr', gap: 16 }}>
      <div>
        <SearchBar value={q} onChange={(v) => update({ q: v })} />
        <div style={{ marginTop: 8 }}>
          <SearchFilters
            hasGeo={hasGeo}
            sort={sort}
            order={order}
            perPage={perPage}
            onChange={(n) => update(n)}
          />
        </div>

        <div style={{ minHeight: 200, marginTop: 12 }}>
          {isLoading && <p aria-live="polite">Carregando...</p>}
          {isError && (
            <div role="alert">
              <p>Ocorreu um erro ao buscar contatos.</p>
              <button onClick={() => refetch()}>Tentar novamente</button>
            </div>
          )}
          {data && (
            <ContactList items={data.data} selectedId={selectedId} onSelect={(id) => onSelect(id)} />
          )}
        </div>

        {data && data.meta && (
          <div style={{ display: 'flex', gap: 8, marginTop: 8 }}>
            <button disabled={page <= 1} onClick={() => update({ page: page - 1 })}>
              Anterior
            </button>
            <span>
              Página {data.meta.page} de {data.meta.last_page}
            </span>
            <button disabled={page >= data.meta.last_page} onClick={() => update({ page: page + 1 })}>
              Próxima
            </button>
          </div>
        )}
      </div>

      <div>
        {(() => {
          const pins = (data?.data || [])
            .filter((c) => c.address?.lat != null && c.address?.lng != null)
            .map((c) => ({
              id: c.id,
              name: c.name,
              lat: c.address!.lat as number,
              lng: c.address!.lng as number,
              localidade: c.address?.localidade ?? null,
              uf: c.address?.uf ?? null,
            }))
          return (
            <MapCanvas
              pins={pins}
              selectedId={selectedId}
              onSelect={(id) => onSelect(id)}
              boundsKey={`${page}-${pins.length}`}
              height="calc(100vh - 128px)"
              cluster={pins.length > 200}
              showDetailAction
            />
          )
        })()}
      </div>
    </div>
  )
}
