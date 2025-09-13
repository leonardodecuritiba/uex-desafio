import { describe, it, expect, vi, beforeEach } from 'vitest'
import { render, screen, fireEvent, waitFor } from '@testing-library/react'
import { QueryClient, QueryClientProvider } from '@tanstack/react-query'
import { MemoryRouter, Route, Routes } from 'react-router-dom'
import SearchContactsPage from '../pages/SearchContactsPage'
import { api } from '../../../shared/api'

vi.mock('../../../shared/api', async (orig) => {
  const mod = await orig()
  return {
    ...mod,
    api: {
      get: vi.fn(),
    },
  }
})

function renderWithProviders(initialPath = '/contacts') {
  const qc = new QueryClient()
  return render(
    <QueryClientProvider client={qc}>
      <MemoryRouter initialEntries={[initialPath]}>
        <Routes>
          <Route path="/contacts" element={<SearchContactsPage />} />
        </Routes>
      </MemoryRouter>
    </QueryClientProvider>
  )
}

const dataset = {
  data: [
    {
      id: 1,
      name: 'Maria Silva',
      cpf: '52998224725',
      address: { localidade: 'Curitiba', uf: 'PR', lat: -25.42, lng: -49.27 },
    },
    { id: 2, name: 'João Souza', cpf: null, address: { localidade: 'Curitiba', uf: 'PR', lat: null, lng: null } },
  ],
  meta: { page: 1, per_page: 20, total: 2, last_page: 1, sort: 'created_at', order: 'desc' },
}

describe('SearchContactsPage', () => {
  beforeEach(() => {
    vi.useFakeTimers()
    vi.setSystemTime(new Date('2025-01-01'))
    ;(api.get as any).mockReset()
  })

  it('F1: digitar "maria" busca e lista/pins coerentes', async () => {
    ;(api.get as any).mockResolvedValueOnce({ data: dataset })

    renderWithProviders()
    const input = screen.getByLabelText('buscar')
    fireEvent.change(input, { target: { value: 'maria' } })
    vi.advanceTimersByTime(450)

    // primeira chamada
    await waitFor(() => expect((api.get as any).mock.calls[0][0]).toBe('/api/contacts'))
    expect((api.get as any).mock.calls[0][1].params.q).toBe('maria')

    // renderiza itens
    expect(await screen.findByText('Maria Silva')).toBeInTheDocument()
    // pins contam apenas itens com lat/lng
    const pins = await screen.findAllByTestId('map-pin')
    expect(pins.length).toBe(1)
  })

  it('F2: 11 dígitos trata como CPF exato', async () => {
    ;(api.get as any).mockResolvedValue({ data: { ...dataset, data: [dataset.data[0]] } })
    renderWithProviders('/contacts?q=52998224725')
    await waitFor(() => screen.getByText('Maria Silva'))
    expect((api.get as any).mock.calls[0][1].params.q).toBe('52998224725')
    // lista só 1
    expect(screen.getAllByRole('listitem').length).toBe(1)
  })

  it('F3: has_geo=true mostra somente itens com coordenadas e pins = itens', async () => {
    ;(api.get as any).mockResolvedValue({ data: { ...dataset, data: [dataset.data[0]] } })
    renderWithProviders('/contacts?has_geo=true')
    await waitFor(() => screen.getByText('Maria Silva'))
    const pins = screen.getAllByTestId('map-pin')
    // apenas um item e um pin
    expect(screen.getAllByRole('listitem').length).toBe(1)
    expect(pins.length).toBe(1)
  })

  it('F6: paginação altera a URL e refaz o fetch', async () => {
    ;(api.get as any).mockResolvedValue({ data: { ...dataset, meta: { ...dataset.meta, page: 1, last_page: 2 } } })
    renderWithProviders('/contacts')
    await waitFor(() => screen.getByText('Página 1 de 2'))
    ;(api.get as any).mockResolvedValue({ data: { ...dataset, meta: { ...dataset.meta, page: 2, last_page: 2 } } })
    fireEvent.click(screen.getByText('Próxima'))
    await waitFor(() => screen.getByText('Página 2 de 2'))
  })
})

