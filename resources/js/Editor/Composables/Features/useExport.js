import { ref } from 'vue'
import { exportToPDF } from '../../Services/export/pdfExporter'
import { exportToWord } from '../../Services/export/wordExporter'
import { EXPORT_FORMATS } from '../../Constants/exportFormats'

export function useExport() {
    const isExporting = ref(false)
    const exportError = ref(null)

    const exportDocument = async (content, format, options = {}) => {
        isExporting.value = true
        exportError.value = null

        try {
            let result

            switch (format) {
                case EXPORT_FORMATS.PDF:
                    result = await exportToPDF(content, options)
                    break
                case EXPORT_FORMATS.WORD:
                    result = await exportToWord(content, options)
                    break
                case EXPORT_FORMATS.MARKDOWN:
                    result = content // Already in markdown-like format
                    downloadAsFile(result, 'document.md', 'text/markdown')
                    break
                case EXPORT_FORMATS.HTML:
                    downloadAsFile(content, 'document.html', 'text/html')
                    break
                default:
                    throw new Error(`Unsupported format: ${format}`)
            }

            return result
        } catch (error) {
            exportError.value = error.message
            console.error('Export failed:', error)
            throw error
        } finally {
            isExporting.value = false
        }
    }

    const downloadAsFile = (content, filename, mimeType) => {
        const blob = new Blob([content], { type: mimeType })
        const url = URL.createObjectURL(blob)
        const link = document.createElement('a')
        link.href = url
        link.download = filename
        link.click()
        URL.revokeObjectURL(url)
    }

    return {
        isExporting,
        exportError,
        exportDocument
    }
}
