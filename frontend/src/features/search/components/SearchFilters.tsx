import { MenuItem, Select, ToggleButton, ToggleButtonGroup } from '@mui/material'

type Props = {
  hasGeo: 'all' | 'true' | 'false'
  sort: 'created_at' | 'name'
  order: 'asc' | 'desc'
  perPage: number
  onChange: (next: Partial<{ hasGeo: 'all' | 'true' | 'false'; sort: 'created_at' | 'name'; order: 'asc' | 'desc'; perPage: number }>) => void
}

export default function SearchFilters({ hasGeo, sort, order, perPage, onChange }: Props) {
  return (
    <div style={{ display: 'flex', gap: 12, alignItems: 'center', flexWrap: 'wrap' }}>
      <ToggleButtonGroup
        value={hasGeo}
        exclusive
        size="small"
        onChange={(_, v) => v && onChange({ hasGeo: v })}
        aria-label="filtro localizacao"
      >
        <ToggleButton value="all">Todos</ToggleButton>
        <ToggleButton value="true">Com localização</ToggleButton>
        <ToggleButton value="false">Sem localização</ToggleButton>
      </ToggleButtonGroup>

      <Select
        size="small"
        value={`${sort}:${order}`}
        onChange={(e) => {
          const [s, o] = String(e.target.value).split(':') as ['created_at' | 'name', 'asc' | 'desc']
          onChange({ sort: s, order: o })
        }}
        displayEmpty
        inputProps={{ 'aria-label': 'ordenacao' }}
      >
        <MenuItem value="created_at:desc">Mais recentes</MenuItem>
        <MenuItem value="created_at:asc">Mais antigos</MenuItem>
        <MenuItem value="name:asc">Nome A-Z</MenuItem>
        <MenuItem value="name:desc">Nome Z-A</MenuItem>
      </Select>

      <Select
        size="small"
        value={perPage}
        onChange={(e) => onChange({ perPage: Number(e.target.value) })}
        inputProps={{ 'aria-label': 'itens por pagina' }}
      >
        {[10, 20, 50].map((n) => (
          <MenuItem key={n} value={n}>
            {n} por página
          </MenuItem>
        ))}
      </Select>
    </div>
  )
}

