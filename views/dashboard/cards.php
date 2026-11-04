<?php
/**
 * Tarjetas de acceso rápido del panel principal.
 *
 * Los valores se muestran como guiones hasta conectar las consultas
 * reales de planillas y recaudación.
 */
?>
<section class="service-grid" aria-label="Servicios principales">

    <article class="service-card">
        <span class="service-card__icon" aria-hidden="true">
            <svg
                xmlns="http://www.w3.org/2000/svg"
                fill="none"
                viewBox="0 0 24 24"
                stroke-width="1.8"
                stroke="currentColor"
            >
                <path
                    stroke-linecap="round"
                    stroke-linejoin="round"
                    d="M19.5 14.25v-2.625a3.375 3.375 0 0 0-3.375-3.375h-1.5A1.125 1.125 0 0 1 13.5 7.125v-1.5A3.375 3.375 0 0 0 10.125 2.25H5.625A1.875 1.875 0 0 0 3.75 4.125v15.75A1.875 1.875 0 0 0 5.625 21.75h12.75a1.875 1.875 0 0 0 1.875-1.875V14.25Z"
                />
                <path
                    stroke-linecap="round"
                    stroke-linejoin="round"
                    d="M7.5 8.25h3m-3 3h6m-6 3h4.5"
                />
            </svg>
        </span>

        <p class="service-card__eyebrow">Movimiento reciente</p>
        <h2 class="service-card__title">Planillas recientes</h2>
        <p class="service-card__value">—</p>
        <p class="service-card__caption">Última actualización</p>

        <span class="service-card__link is-disabled" aria-disabled="true">
            Ver planillas
            <svg
                xmlns="http://www.w3.org/2000/svg"
                fill="none"
                viewBox="0 0 24 24"
                stroke-width="2"
                stroke="currentColor"
                aria-hidden="true"
            >
                <path
                    stroke-linecap="round"
                    stroke-linejoin="round"
                    d="M13.5 4.5 21 12m0 0-7.5 7.5M21 12H3"
                />
            </svg>
        </span>
    </article>

    <article class="service-card">
        <span class="service-card__icon" aria-hidden="true">
            <svg
                xmlns="http://www.w3.org/2000/svg"
                fill="none"
                viewBox="0 0 24 24"
                stroke-width="1.8"
                stroke="currentColor"
            >
                <path
                    stroke-linecap="round"
                    stroke-linejoin="round"
                    d="M3 13.125C3 12.504 3.504 12 4.125 12h2.25c.621 0 1.125.504 1.125 1.125v6.75C7.5 20.496 6.996 21 6.375 21h-2.25A1.125 1.125 0 0 1 3 19.875v-6.75ZM9.75 8.625c0-.621.504-1.125 1.125-1.125h2.25c.621 0 1.125.504 1.125 1.125v11.25c0 .621-.504 1.125-1.125 1.125h-2.25a1.125 1.125 0 0 1-1.125-1.125V8.625ZM16.5 4.125c0-.621.504-1.125 1.125-1.125h2.25C20.496 3 21 3.504 21 4.125v15.75c0 .621-.504 1.125-1.125 1.125h-2.25a1.125 1.125 0 0 1-1.125-1.125V4.125Z"
                />
            </svg>
        </span>

        <p class="service-card__eyebrow">Resumen financiero</p>
        <h2 class="service-card__title">Control de recaudación</h2>
        <p class="service-card__value service-card__value--currency">Bs —</p>
        <p class="service-card__caption">Total del mes</p>

        <span class="service-card__link is-disabled" aria-disabled="true">
            Ver recaudación
            <svg
                xmlns="http://www.w3.org/2000/svg"
                fill="none"
                viewBox="0 0 24 24"
                stroke-width="2"
                stroke="currentColor"
                aria-hidden="true"
            >
                <path
                    stroke-linecap="round"
                    stroke-linejoin="round"
                    d="M13.5 4.5 21 12m0 0-7.5 7.5M21 12H3"
                />
            </svg>
        </span>
    </article>

    <article class="service-card">
        <span class="service-card__icon" aria-hidden="true">
            <svg
                xmlns="http://www.w3.org/2000/svg"
                fill="none"
                viewBox="0 0 24 24"
                stroke-width="1.8"
                stroke="currentColor"
            >
                <path
                    stroke-linecap="round"
                    stroke-linejoin="round"
                    d="M12 6v6h4.5m4.5 0a9 9 0 1 1-18 0 9 9 0 0 1 18 0Z"
                />
            </svg>
        </span>

        <p class="service-card__eyebrow">Seguimiento operativo</p>
        <h2 class="service-card__title">Planillas pendientes</h2>
        <p class="service-card__value">—</p>
        <p class="service-card__caption">Por revisar</p>

        <span class="service-card__link is-disabled" aria-disabled="true">
            Ver pendientes
            <svg
                xmlns="http://www.w3.org/2000/svg"
                fill="none"
                viewBox="0 0 24 24"
                stroke-width="2"
                stroke="currentColor"
                aria-hidden="true"
            >
                <path
                    stroke-linecap="round"
                    stroke-linejoin="round"
                    d="M13.5 4.5 21 12m0 0-7.5 7.5M21 12H3"
                />
            </svg>
        </span>
    </article>

</section>
