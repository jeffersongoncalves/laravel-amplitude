# Changelog

All notable changes to this project will be documented in this file.

## 1.2.0 - 2026-11-17

### Cookie consent

With [laravel-cookie-consent](https://github.com/jeffersongoncalves/laravel-cookie-consent) 4.6+ installed and `block_scripts` on, the scripts wait until the visitor allows their cookie category (rendered as `type="text/plain" data-category="…"`, run with their CSP nonce once allowed). Nothing changes without it: this package still does not require laravel-cookie-consent.

## 1.1.0 - 2026-10-09

- Every `<script>` rendered by the package carries Laravel's Vite CSP nonce when the app sets one (e.g. via laravel-security-headers), so nonce-based `script-src` policies work without `'unsafe-inline'`.

## 1.0.0 - 2026-09-05

**Full Changelog**: https://github.com/jeffersongoncalves/laravel-amplitude/commits/1.0.0

## [Unreleased]
