const REF_KEY = 'ref_code'

const normalizeRef = (ref) => {
  const r = String(ref || '').trim().toUpperCase()
  return /^ADS\d+$/.test(r) ? r : ''
}

export const storeRefFromUrl = () => {
  try {
    if (typeof window === 'undefined') return ''
    const qs = new URLSearchParams(window.location.search)
    const ref = normalizeRef(qs.get('ref'))
    if (ref) {
      localStorage.setItem(REF_KEY, ref)
      return ref
    }
  } catch (_) {}
  return ''
}

export const getStoredRef = () => {
  try {
    if (typeof window === 'undefined') return ''
    return normalizeRef(localStorage.getItem(REF_KEY))
  } catch (_) {
    return ''
  }
}

export const setStoredRef = (ref) => {
  try {
    if (typeof window === 'undefined') return ''
    const r = normalizeRef(ref)
    if (r) localStorage.setItem(REF_KEY, r)
    return r
  } catch (_) {
    return ''
  }
}

export const withStoredRef = (to) => {
  // For router-link `:to="withStoredRef('/register')"`
  // If ref already present in the URL, we keep it.
  const ref = getStoredRef()
  if (!ref) return to
  if (!to) return to
  try {
    if (typeof to === 'object') {
      const q = { ...(to.query || {}) }
      if (!q.ref) q.ref = ref
      return { ...to, query: q }
    }
    const raw = String(to)
    const u = new URL(raw, window.location.origin)
    if (!u.searchParams.get('ref')) u.searchParams.set('ref', ref)
    return u.pathname + u.search + u.hash
  } catch (_) {
    return to
  }
}

