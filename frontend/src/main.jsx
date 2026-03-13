import { StrictMode } from 'react'
import { createRoot } from 'react-dom/client'
import './index.css'
import App from './App.jsx'
import { GlobalSystemProvider } from './context/GlobalSystemContext';
import './bootstrap';

createRoot(document.getElementById('root')).render(
  <StrictMode>
    <GlobalSystemProvider>
      <App />
    </GlobalSystemProvider>
  </StrictMode>,
)
