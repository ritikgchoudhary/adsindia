export const preopenPaymentTab = () => {
  try {
    const w = window.open('about:blank', '_blank')
    if (!w) return null
    try {
      w.opener = null
    } catch (_) {}
    try {
      // Lightweight placeholder (optional)
      w.document.title = 'Opening payment...'
    } catch (_) {}
    return w
  } catch (_) {
    return null
  }
}

export const navigatePaymentTab = (tab, paymentUrl) => {
  const url = String(paymentUrl || '').trim()
  if (!url) return { opened: false, blocked: false }

  // If we couldn't pre-open, fall back to same tab
  if (!tab) {
    window.location.href = url
    return { opened: false, blocked: false }
  }

  try {
    tab.location.href = url
    return { opened: true, blocked: false }
  } catch (_) {
    window.location.href = url
    return { opened: false, blocked: false }
  }
}

// Backwards compatible helper (NOTE: if called after an await, popup blockers may block it)
export const openPaymentInNewTab = (paymentUrl) => {
  const tab = preopenPaymentTab()
  if (!tab) return { opened: false, blocked: true }
  return navigatePaymentTab(tab, paymentUrl)
}

