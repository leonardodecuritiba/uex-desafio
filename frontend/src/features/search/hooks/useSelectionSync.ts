import { useState, useCallback } from 'react'

export function useSelectionSync() {
  const [selectedId, setSelectedId] = useState<number | null>(null)

  const onSelect = useCallback((id: number | null) => setSelectedId(id), [])

  return { selectedId, onSelect }
}

