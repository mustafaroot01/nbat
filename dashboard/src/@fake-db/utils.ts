export const paginationMeta = (options: { page: number; itemsPerPage: number }, total: number) => {
  const start = (options.page - 1) * options.itemsPerPage + 1
  const end = Math.min(options.page * options.itemsPerPage, total)

  return `Showing ${start} to ${end} of ${total} entries`
}
