import { describe, it, expect, vi } from 'vitest'
import { render, screen, fireEvent, waitFor } from '@testing-library/react'
import ContactForm from '../../contacts/components/ContactForm'

vi.mock('../../contacts/api/address', () => ({
  fetchCep: vi.fn(async (cep: string) => {
    if (cep === '80000000') {
      return { cep: '80000000', logradouro: 'Rua Exemplo', bairro: 'Centro', localidade: 'Curitiba', uf: 'PR' }
    }
    throw new Error('CEP_NOT_FOUND')
  }),
}))

describe('CEP lookup', () => {
  it('fills address fields when CEP is found', async () => {
    render(<ContactForm onSubmit={async () => {}} />)
    fireEvent.change(screen.getByLabelText(/CEP/i), { target: { value: '80000000' } })
    fireEvent.click(screen.getByRole('button', { name: /Buscar CEP/i }))

    await waitFor(() => expect((screen.getByLabelText(/Logradouro/i) as HTMLInputElement).value).toBe('Rua Exemplo'))
    expect((screen.getByLabelText(/UF/i) as HTMLInputElement).value).toBe('PR')
  })
})

