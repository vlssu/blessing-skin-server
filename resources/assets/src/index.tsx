import * as React from 'react'
import * as ReactDOM from 'react-dom'
import $ from 'jquery'
import './scripts/app'
import routes from './scripts/route'

Object.assign(window, { React, ReactDOM, $ })

if (blessing.route.startsWith('admin')) {
  const entry = document.querySelector('[href="#launch-cli"]')
  entry?.addEventListener('click', async () => {
    const { launch } = await import('./scripts/cli')
    launch()
  })
}

const route = routes.find((route) =>
  new RegExp(`^${route.path}$`, 'i').test(blessing.route),
)
if (route) {
  if (route.module) {
    Promise.all(route.module.map((m) => m()))
  }
  if (route.react) {
    const Component = React.lazy(
      route.react as () => Promise<{ default: React.ComponentType }>,
    )
    const Root = () => (
      <React.StrictMode>
        <React.Suspense fallback={route.frame?.() ?? ''}>
          <Component />
        </React.Suspense>
      </React.StrictMode>
    )
    const c =
      typeof route.el === 'string' ? document.querySelector(route.el) : route.el
    ReactDOM.render(<Root />, c)
  }
}
