import { Card, CardContent, Typography } from '@mui/material'
import MapCanvas from '../../map/components/MapCanvas'

type Props = {
  name: string
  address?: { lat?: number | null; lng?: number | null; localidade?: string | null; uf?: string | null } | null
}

export default function ContactMapCard({ name, address }: Props) {
  const lat = address?.lat
  const lng = address?.lng
  const hasGeo = lat != null && lng != null
  if (!hasGeo) return null

  const pins = [{ id: 0, name, lat: lat as number, lng: lng as number, localidade: address?.localidade ?? null, uf: address?.uf ?? null }]

  return (
    <Card>
      <CardContent>
        <Typography variant="h6" component="h2">
          Localização
        </Typography>
        <MapCanvas pins={pins} height={360} cluster={false} showDetailAction={false} />
      </CardContent>
    </Card>
  )
}
