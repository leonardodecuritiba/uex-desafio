#!/usr/bin/env sh
set -eu

if [ -f ".bootstrap.done" ]; then
  echo "[frontend] Already bootstrapped. Skipping."
  exit 0
fi

if [ ! -f package.json ]; then
  echo "[frontend] Creating Vite React (TS) app..."
  npm create vite@latest . -- --template react-ts
fi

echo "[frontend] Installing dependencies (MUI, React Query, Zod, Router)..."
npm i @mui/material @emotion/react @emotion/styled @mui/icons-material @tanstack/react-query zod react-router-dom axios

read -p "Deseja continuar a execução? (s/N) " confirm
case "$confirm" in
  [sS]|[sS][iI][mM])
    echo "➡️  Continuando..."
    ;;
  *)
    echo "❌ Execução abortada pelo usuário."
    exit 1
    ;;
esac

echo "[frontend] Installing dev dependencies (ESLint, Vitest, RTL)..."
npm i -D eslint @typescript-eslint/eslint-plugin @typescript-eslint/parser vitest jsdom @testing-library/react @testing-library/user-event @testing-library/jest-dom @vitejs/plugin-react

read -p "Deseja continuar a execução? (s/N) " confirm
case "$confirm" in
  [sS]|[sS][iI][mM])
    echo "➡️  Continuando..."
    ;;
  *)
    echo "❌ Execução abortada pelo usuário."
    exit 1
    ;;
esac

echo "VITE_API_URL=http://localhost:8080" > .env.example
if [ ! -f .env ]; then cp .env.example .env; fi

# Basic quality configs
cat > .eslintrc.cjs <<'JSON'
module.exports = {
  root: true,
  parser: '@typescript-eslint/parser',
  plugins: ['@typescript-eslint'],
  extends: ['eslint:recommended', 'plugin:@typescript-eslint/recommended', 'plugin:react-hooks/recommended'],
};
JSON

read -p "Deseja continuar a execução? (s/N) " confirm
case "$confirm" in
  [sS]|[sS][iI][mM])
    echo "➡️  Continuando..."
    ;;
  *)
    echo "❌ Execução abortada pelo usuário."
    exit 1
    ;;
esac

mkdir -p src/shared
cat > src/shared/api.ts <<'TS'
import axios from 'axios';

export const api = axios.create({
  baseURL: import.meta.env.VITE_API_URL || 'http://localhost:8080',
  withCredentials: true,
});
TS

cat > src/main.tsx <<'TSX'
import React from 'react'
import ReactDOM from 'react-dom/client'
import { QueryClient, QueryClientProvider } from '@tanstack/react-query'
import { CssBaseline, ThemeProvider, createTheme } from '@mui/material'
import App from './App.tsx'

const queryClient = new QueryClient()
const theme = createTheme({})

ReactDOM.createRoot(document.getElementById('root')!).render(
  <React.StrictMode>
    <ThemeProvider theme={theme}>
      <CssBaseline />
      <QueryClientProvider client={queryClient}>
        <App />
      </QueryClientProvider>
    </ThemeProvider>
  </React.StrictMode>,
)
TSX

cat > src/App.tsx <<'TSX'
import { useEffect } from 'react'
import { api } from './shared/api'

function App() {
  useEffect(() => {
    // healthcheck call as smoke-test
    api.get('/health').catch(() => {})
  }, [])

  return (
    <div style={{ padding: 24 }}>
      <h1>UEX Contacts Monolith</h1>
      <p>React + MUI + React Query scaffold</p>
    </div>
  )
}

export default App
TSX

cat > vite.config.ts <<'TS'
import { defineConfig } from 'vite'
import react from '@vitejs/plugin-react'

export default defineConfig({
  plugins: [react()],
  server: {
    host: '0.0.0.0',
    port: 5173,
    strictPort: true,
  },
})
TS

touch .bootstrap.done
echo "[frontend] Done."
