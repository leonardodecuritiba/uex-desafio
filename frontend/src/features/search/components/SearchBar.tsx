import { useEffect, useMemo, useState } from 'react'
import TextField from '@mui/material/TextField'

type Props = {
  value: string
  onChange: (v: string) => void
}

export default function SearchBar({ value, onChange }: Props) {
  const [local, setLocal] = useState(value)
  useEffect(() => setLocal(value), [value])

  const debounced = useDebounced(local, 400)
  useEffect(() => {
    if (debounced !== value) onChange(debounced)
  }, [debounced])

  return (
    <div style={{ display: 'flex', gap: 8 }}>
      <TextField
        fullWidth
        size="small"
        label="Buscar por nome ou CPF"
        placeholder="Digite o nome ou CPF"
        value={local}
        onChange={(e) => setLocal(e.target.value)}
        inputProps={{ 'aria-label': 'buscar' }}
      />
    </div>
  )
}

function useDebounced<T>(value: T, delay: number): T {
  const [deb, setDeb] = useState(value)
  useEffect(() => {
    const id = setTimeout(() => setDeb(value), delay)
    return () => clearTimeout(id)
  }, [value, delay])
  return deb
}

