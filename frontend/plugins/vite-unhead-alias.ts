import type { Plugin } from 'vite'

/**
 * Plugin Vite pour corriger l'import getActiveHead depuis unhead
 * @nuxtjs/i18n essaie d'importer getActiveHead qui n'existe pas dans unhead v2
 */
export default function unheadAliasPlugin(): Plugin {
  return {
    name: 'unhead-alias-fix',
    enforce: 'pre',
    resolveId(id) {
      // Intercepter les imports de getActiveHead depuis unhead
      if (id.includes('@nuxtjs/i18n') || id.includes('i18n')) {
        return null // Laisser Vite traiter normalement
      }
      return null
    },
    transform(code, id) {
      // Remplacer l'import problématique dans les fichiers @nuxtjs/i18n
      if (id.includes('@nuxtjs/i18n') && code.includes('getActiveHead')) {
        // Remplacer l'import par une fonction mock qui ne fait rien
        const fixedCode = code.replace(
          /import\s*{\s*([^}]*getActiveHead[^}]*)\s*}\s*from\s*['"]unhead['"]/g,
          `// getActiveHead mock - compatibility fix
const getActiveHead = () => ({});`
        ).replace(
          /import\s*{\s*([^}]*)\s*getActiveHead\s*([^}]*)\s*}\s*from\s*['"]unhead['"]/g,
          (match, before, after) => {
            // Si getActiveHead est avec d'autres imports, les séparer
            const otherImports = before + after
            if (otherImports.trim()) {
              return `import { ${otherImports.trim()} } from 'unhead';
const getActiveHead = () => ({});`
            }
            return `const getActiveHead = () => ({});`
          }
        )
        
        if (fixedCode !== code) {
          return {
            code: fixedCode,
            map: null,
          }
        }
      }
      
      return null
    },
  }
}
