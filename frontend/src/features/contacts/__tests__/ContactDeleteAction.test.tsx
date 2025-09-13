import { describe, it, expect, vi } from 'vitest'
import { render, screen, fireEvent, waitFor } from '@testing-library/react'
import ContactDeleteAction from '../../contacts/components/ContactDeleteAction'

vi.mock('../../contacts/hooks/useDeleteContact', () => {
  return {
    useDeleteContact: () => ({
      isPending: false,
      mutateAsync: vi.fn(async () => true),
    }),
  }
})

describe('ContactDeleteAction', () => {
  it('opens modal and calls delete on confirm', async () => {
    const onDeleted = vi.fn()
    render(<ContactDeleteAction id={123} onDeleted={onDeleted} />)
    fireEvent.click(screen.getByRole('button', { name: /Excluir/i }))
    expect(screen.getByRole('dialog')).toBeInTheDocument()
    fireEvent.click(screen.getByRole('button', { name: /Excluir/i }))
    await waitFor(() => expect(onDeleted).toHaveBeenCalled())
  })
})

