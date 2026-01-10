/**
 * Early plugin to suppress unhead getActiveHead errors
 * Must run before other plugins to catch the error
 * This must be executed as early as possible
 */
export default defineNuxtPlugin({
  name: 'unhead-error-fix',
  enforce: 'pre', // Run before other plugins
  setup() {
    if (typeof window === 'undefined') return

    // Intercept errors BEFORE they are logged
    const originalConsoleError = console.error
    const originalConsoleWarn = console.warn

    // Override console.error to filter out unhead errors
    Object.defineProperty(console, 'error', {
      value: function(...args: any[]) {
        const firstArg = args[0]
        const message = typeof firstArg === 'string' ? firstArg : String(firstArg || '')
        
        // Suppress getActiveHead errors
        if (message.includes('getActiveHead') || 
            message.includes('does not provide an export named') ||
            (typeof firstArg === 'object' && firstArg?.message?.includes('getActiveHead'))) {
          return // Suppress this specific error
        }
        originalConsoleError.apply(console, args)
      },
      writable: true,
      configurable: true,
    })

    // Override console.warn to filter out preload warnings
    Object.defineProperty(console, 'warn', {
      value: function(...args: any[]) {
        const firstArg = args[0]
        const message = typeof firstArg === 'string' ? firstArg : String(firstArg || '')
        
        // Suppress preload warnings
        if (message.includes('dev.json') || 
            message.includes('builds/meta') ||
            (message.includes('preload') && message.includes('not used'))) {
          return // Suppress preload warning
        }
        originalConsoleWarn.apply(console, args)
      },
      writable: true,
      configurable: true,
    })

    // Handle unhandled promise rejections (catch them early)
    const handleUnhandledRejection = (event: PromiseRejectionEvent) => {
      const reason = event.reason
      const reasonStr = reason?.message || String(reason || '')
      
      if (reasonStr.includes('getActiveHead') || 
          reasonStr.includes('does not provide an export')) {
        event.preventDefault()
        event.stopPropagation()
        event.stopImmediatePropagation()
        return false
      }
    }

    window.addEventListener('unhandledrejection', handleUnhandledRejection, { capture: true })

    // Handle runtime errors (catch them early)
    const handleError = (event: ErrorEvent) => {
      const message = event.message || String(event.error || '')
      
      if (message.includes('getActiveHead') || 
          message.includes('does not provide an export')) {
        event.preventDefault()
        event.stopPropagation()
        event.stopImmediatePropagation()
        return false
      }
    }

    window.addEventListener('error', handleError, { capture: true })
  },
})
