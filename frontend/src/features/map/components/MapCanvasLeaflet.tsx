import 'leaflet/dist/leaflet.css'
import { MapContainer, TileLayer, Popup, Marker, useMap } from 'react-leaflet'
import { useEffect, useMemo } from 'react'

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

function FitBounds({ pins, boundsKey }: { pins: Pin[]; boundsKey?: string | number | null }) {
  const map = useMap()
  const bounds = useMemo(() => {
    if (!pins.length) return null
    const latlngs = pins.map((p) => [p.lat, p.lng]) as [number, number][]
    return latlngs
  }, [pins])

  useEffect(() => {
    if (!bounds || bounds.length === 0) return
    map.fitBounds(bounds, { padding: [24, 24] })
  }, [boundsKey])

  return null
}

export default function MapCanvasLeaflet({ pins, selectedId = null, onSelect, boundsKey, height = 500, showDetailAction = true }: Props) {
  const tileUrl = import.meta.env.VITE_MAP_TILES_URL || 'https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png'

  return (
    <div style={{ height }}>
      <MapContainer center={[ -15.78, -47.93 ]} zoom={4} style={{ height: '100%', width: '100%' }}>
        <TileLayer url={tileUrl} />
        <FitBounds pins={pins} boundsKey={boundsKey ?? pins.length} />
        {pins.map((m) => (
          // <CircleMarker
          //   key={m.id}
          //   center={[m.lat, m.lng]}
          //   radius={10}
          //   pathOptions={{ color: selectedId === m.id ? '#1976d2' : '#e53935', weight: selectedId === m.id ? 3 : 1 }}
          //   eventHandlers={{
          //     click: () => onSelect?.(m.id),
          //   }}
          // >
          //   <Popup autoPan>
          //     <div>
          //       <strong>{m.name}</strong>
          //       <div style={{ fontSize: 12, color: '#666' }}>
          //         {(m.localidade || m.uf) ? `${m.localidade ?? ''}${m.localidade && m.uf ? '/' : ''}${m.uf ?? ''}` : null}
          //       </div>
          //       <div style={{ marginTop: 8 }}>
          //         {showDetailAction ? <a href={`/contacts/${m.id}`}>Ver detalhes</a> : null}
          //       </div>
          //     </div>
          //   </Popup>
          // </CircleMarker>

          <Marker
            key={m.id}
            position={[m.lat, m.lng]}
            eventHandlers={{
              click: () => onSelect?.(m.id),
            }}
          >
            <Popup autoPan>
              <div>
                <strong>{m.name}</strong>
                <div style={{ fontSize: 12, color: '#666' }}>
                  {(m.localidade || m.uf) ? `${m.localidade ?? ''}${m.localidade && m.uf ? '/' : ''}${m.uf ?? ''}` : null}
                </div>
                <div style={{ marginTop: 8 }}>
                  {showDetailAction ? <a href={`/contacts/${m.id}`}>Ver detalhes</a> : null}
                </div>
              </div>
            </Popup>
          </Marker>
        ))}
      </MapContainer>
    </div>
  )
}
