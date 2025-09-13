/* eslint-disable @typescript-eslint/no-explicit-any */
import { describe, it, expect, vi } from 'vitest'
import { render, screen, fireEvent } from '@testing-library/react'
import ContactEditForm from '../../contacts/components/ContactEditForm'

const initial = {
  id: 1,
  name: 'Maria',
  cpf: '52998224725',
  email: 'maria@example.com',
  phone: '4199',
  address: {
    cep: '80000000',
    logradouro: 'Rua A',
    numero: '123',
    complemento: 'Ap 1',
    bairro: 'Centro',
    localidade: 'Curitiba',
    uf: 'PR',
  },
  updated_at: '2025-09-12T18:12:34Z',
}

describe('ContactEditForm', () => {
  it('sends only changed fields on submit', async () => {
    const onSubmit = vi.fn()
    render(<ContactEditForm initialValues={initial as any} onSubmit={onSubmit} />)

    fireEvent.change(screen.getByLabelText(/Nome/i), { target: { value: 'Maria Silva' } })
    fireEvent.click(screen.getByRole('button', { name: /Salvar alterações/i }))

    expect(onSubmit).toHaveBeenCalled()
    const patch = onSubmit.mock.calls[0][0]
    expect(patch).toEqual(expect.objectContaining({ name: 'Maria Silva', version: initial.updated_at }))
    expect(patch.address).toBeUndefined()
  })

  it('sends null for removed address subfield', async () => {
    const onSubmit = vi.fn()
    render(<ContactEditForm initialValues={initial as any} onSubmit={onSubmit} />)

    // clear complemento
    fireEvent.change(screen.getByLabelText(/Complemento/i), { target: { value: '' } })
    fireEvent.click(screen.getByRole('button', { name: /Salvar alterações/i }))

    const patch = onSubmit.mock.calls[0][0]
    expect(patch.address).toEqual(expect.objectContaining({ complemento: null }))
  })
})

