// Wrapper que usa react-leaflet em runtime normal e fallback no modo de teste (Vitest/JSDOM)
import MapCanvasLeaflet from './MapCanvasLeaflet'
import MapCanvasFallback from './MapCanvasFallback'

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

export default function MapCanvas(props: Props) {
  if (import.meta.env.MODE === 'test') {
    return <MapCanvasFallback {...props} />
  }
  return <MapCanvasLeaflet {...props} />
}
