import type { VerticalNavItems } from '@/@layouts/types'
import appAndPages from './app-and-pages'
import dashboard from './dashboard'

export default [...dashboard, ...appAndPages] as VerticalNavItems
