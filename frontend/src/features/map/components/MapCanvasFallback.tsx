type Pin = {
  id: number
  name: string
  lat: number
  lng: number
  localidade?: string | null
  uf?: string | null
}

type Props = {
  pins: Pin[]
  selectedId?: number | null
  onSelect?: (id: number) => void
  boundsKey?: string | number | null
  height?: number | string
  cluster?: boolean
  showDetailAction?: boolean
}

export default function MapCanvasFallback({ pins, selectedId = null, onSelect, boundsKey, height = 400, showDetailAction = true }: Props) {
  const selected = pins.find((p) => p.id === selectedId) || null
  return (
    <div aria-label="mapa" data-bounds-key={boundsKey ?? ''} style={{ border: '1px solid #ddd', borderRadius: 8, minHeight: 0, height, position: 'relative' }}>
      <div style={{ padding: 8, fontSize: 12, color: '#555' }}>Pins: {pins.length}</div>
      <div>
        {pins.map((m) => (
          <div
            key={m.id}
            data-testid="map-pin"
            onClick={() => onSelect?.(m.id)}
            style={{
              display: 'inline-block',
              margin: 6,
              padding: '4px 6px',
              borderRadius: 4,
              border: '1px solid #aaa',
              background: selectedId === m.id ? '#e3f2fd' : '#fff',
              cursor: 'pointer',
            }}
            title={m.name}
          >
            📍 {m.name}
          </div>
        ))}
      </div>

      {selected && (
        <div role="dialog" aria-label="popup" style={{ position: 'absolute', right: 12, bottom: 12, background: '#fff', border: '1px solid #ccc', borderRadius: 6, padding: 12, boxShadow: '0 2px 8px rgba(0,0,0,0.1)' }}>
          <strong>{selected.name}</strong>
          <div style={{ fontSize: 12, color: '#666' }}>
            {(selected.localidade || selected.uf) ? `${selected.localidade ?? ''}${selected.localidade && selected.uf ? '/' : ''}${selected.uf ?? ''}` : null}
          </div>
          <div style={{ marginTop: 8 }}>
            {showDetailAction ? <a href={`/contacts/${selected.id}`}>Ver detalhes</a> : null}
          </div>
        </div>
      )}
    </div>
  )
}
