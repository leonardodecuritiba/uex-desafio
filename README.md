# UEX Contacts Monolith

Monólito Laravel 12 (API) + React (MUI) + PostgreSQL, orquestrado por Docker Compose. Clean Architecture, SOLID e TDD. Domínio: gerenciador de contatos com endereço, mapa e autenticação.

Principais Tecnologias
- Backend: PHP 8.3, Laravel 12, Sanctum, PostgreSQL 15, Redis 7
- Frontend: Vite + React + TypeScript, MUI, React Query, Zod
- Mapa: react-leaflet (OSM tiles) com fallback em testes
- Infra: Nginx, Mailpit, Docker Compose

Arquitetura (pastas-chave)
- backend/app/Domain: entidades, interfaces (ports)
- backend/app/Application: casos de uso e orquestrações
- backend/app/Infrastructure: HTTP, validações, repositórios, integrações externas
- frontend/src/features: auth, contacts, search, map, shared

Principais Funcionalidades
- Autenticação: registrar, login/logout, esqueci/resetar senha, perfil
- Contatos: criar, visualizar, editar, remover
- Endereço: ViaCEP via proxy `/api/addresses/search` (backend)
- Geocodificação: Google Geocoding server-side (criação e edição quando endereço muda)
- Busca: por nome/CPF, paginação/ordenação, filtro `has_geo`
- Mapas: página dedicada `/contacts/map` com paginação progressiva; mapa reutilizado nas telas de Busca e Detalhe
- Exclusão de conta: confirma senha, revoga sessões e apaga dados

Validações de Negócio (PRD/TDD)
- CPF: obrigatório, 11 dígitos, algoritmo oficial, único por usuário
- Endereço (criação e estado final na edição):
  - Obrigatórios: `cep(8)`, `logradouro(2..150)`, `numero(1..20)`, `bairro(2..100)`, `localidade(2..120)`, `uf(2)`
  - Opcional: `complemento`
- Erros 422 padronizados: `{ errors: [{ field, message }] }`

Endpoints (resumo)
- Auth: `POST /api/auth/register|login|logout`, `POST /api/auth/forgot-password`, `POST /api/auth/reset-password`, `GET /api/auth/me`
- Contatos:
  - `GET /api/contacts?search|q|cpf|has_geo|page|per_page|sort|order`
  - `POST /api/contacts` (CPF + endereço obrigatórios)
  - `GET /api/contacts/{id}`
  - `PATCH /api/contacts/{id}` (edição parcial; estado final deve permanecer válido)
  - `DELETE /api/contacts/{id}`
- ViaCEP proxy: `GET /api/addresses/search?uf=PR&city=Curitiba&q=Rua...`
- Conta: `DELETE /api/account` (exige senha)
- Healthcheck: `GET /api/health`

Front-end (rotas principais)
- `/login`, `/register`, `/forgot-password`, `/reset-password`
- `/contacts` (busca + lista + mapa à direita)
- `/contacts/new` (criar)
- `/contacts/:id` (detalhe + mapa)
- `/contacts/:id/edit` (editar)
- `/contacts/map` (visualização no mapa)
- `/account/delete` (excluir conta)

Execução Local
1) Copie `.env.example` para `.env` e ajuste se necessário.
2) `make init` – builda imagens, instala deps e prepara apps.
3) `make up` – sobe serviços.
- Backend: http://localhost:8080
- Frontend: http://localhost:5173
- Mailpit: http://localhost:8025

Comandos úteis
- `make artisan ARGS="migrate"` – roda migrations
- `make test` – testes backend (PHPUnit)
- `make npm-frontend ARGS="run test"` – testes frontend (Vitest)
- `make npm-frontend ARGS="run dev"` – frontend em desenvolvimento

Variáveis de Ambiente (principais)
- Backend (arquivo `.env`):
  - `GEOCODE_ON_CREATE=true|false`
  - `GEOCODE_ON_UPDATE=true|false`
  - `GEOCODE_TIMEOUT_MS=2500`
  - `GEOCODE_RETRIES=1`
  - `GEOCODE_CACHE_TTL=604800`
  - `GOOGLE_MAPS_API_KEY=...` (nunca expor no frontend)
- Frontend:
  - `VITE_API_URL=http://localhost:8080` (base da API)
  - `VITE_MAP_TILES_URL=https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png`

Observabilidade e Segurança
- Healthcheck simples (`/api/health`), logs sem PII sensível
- Sanctum com cookies httpOnly e CSRF; CORS restrito
- Rate-limit leve e cache para geocodificação

Padrões e Qualidade
- Clean Architecture (Domain/Application/Infrastructure)
- TDD: testes de domínio, aplicação, HTTP e UI
- Linters/formatadores: Laravel Pint (backend) e ESLint (frontend)

Erros (exemplos 422)
```
{
  "errors": [
    { "field": "cpf", "message": "CPF inválido." },
    { "field": "address.cep", "message": "CEP deve conter 8 dígitos." }
  ]
}
```

Dicas
- Para mapas em produção, defina `VITE_MAP_TILES_URL` (tiles confiáveis) e `GOOGLE_MAPS_API_KEY` no backend para geocodificação.
- O componente `MapCanvas` usa react-leaflet em runtime e um fallback em testes (modo `test`).
