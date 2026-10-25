# Changelog

This changelog covers **only the two most recent published GitHub releases** of `local_ai_bridge`. Each entry is grounded in the changes between its published release tag and the previous **published** release tag. The intermediate tag `1.2.1` has no published GitHub release and is therefore included in the comparison for `1.2.2`, rather than given its own entry.

## [1.2.3] - 2026-10-21

Comparison: [1.2.2...1.2.3](https://github.com/EduardoKrausME/moodle-local_ai_bridge/compare/1.2.2...1.2.3)

### Security and tenant isolation

- Tenant access now requires an explicit user assignment in `local_ai_bridge_user`; editable Moodle `institution` and `department` fields cannot independently grant access to another tenant. API access fails closed when a user belongs to multiple enabled tenants, since requests have no tenant selector.
- Reading an administrative user form no longer provisions membership. Candidate eligibility is checked before loading the user's personal details; an authorized, sesskey-protected administrative save creates the assignment.
- Both text generation and audio transcription verify that acting on behalf of a different Moodle user is reserved for callers with `local/ai_bridge:manageall`.
- Provider endpoint validation now defaults hosted bridges to their official HTTPS hosts; custom gateways require the administrator's `allowcustomhosts` setting. Private, loopback, link-local and local hostnames require `allowprivatehosts`, including local Ollama endpoints. HTTP is restricted to Ollama, and provider HTTP redirects are disabled.
- Consumer-provided system messages and privileged raw provider fields are rejected or filtered, preserving configured instructions, model, message body and limits. Only an allowlist of non-privileged tuning options is accepted per provider.
- Provider error messages are sanitized and bounded before being shown as Moodle exceptions. Gemini credentials are sent via the `x-goog-api-key` header rather than a URL query parameter.

### Credits and availability

- Added tenant-wide locking and atomic quota reservation **before** requests to paid providers. A single reservation covers fallback routes; failures release the reservation, while successful usage is recorded with the reserved charge.
- Improved transaction and rollback handling to avoid leaking transactions and to prevent repeat paid calls when post-response accounting fails.
- Added a site-wide `disableai` setting that blocks new generation and transcription calls, including queued calls checked at execution time.

### Administration, audit and privacy

- Added Moodle audit events for tenant administrator additions/removals, provider-connection changes and credit adjustments.
- Tenant administrator removal now uses a confirmation step followed by a sesskey-protected POST instead of deleting directly from a request parameter.
- Provider privacy metadata now declares data sent to external services: messages and system instructions for OpenAI, Claude, Gemini and Ollama; OpenAI also declares audio and optional language hints.
- Added Brazilian Portuguese privacy-related language strings for the four provider bridges, alongside updates to English and core language strings.

### Maintenance and tests

- Expanded regression coverage for quotas, tenant resolution, permissions, request security, URL validation, privacy metadata, user eligibility and native audit events.
- Updated documentation for tenant assignment, credentials, credits, security settings, consumer request restrictions and provider data transfers.
- Configured release-workflow concurrency, and updated version metadata in `version.php` to release `1.2.3`.

### Upgrade notes

**Action required:** Installations relying on implicit user-to-tenant matching through Moodle profile fields must explicitly assign affected users to their tenants before those users can generate content or transcribe audio. Review multi-tenant assignments, custom API gateway addresses and any local Ollama deployments; the new host restrictions are disabled only through deliberate administrator configuration. Consumer plugins must not rely on overriding purpose system instructions or provider-critical fields through raw options.

## [1.2.2] - 2026-10-15

Comparison: [1.2.0...1.2.2](https://github.com/EduardoKrausME/moodle-local_ai_bridge/compare/1.2.0...1.2.2)

### Changed

- Added the plugin icon at `pix/icon.png`.
- Advanced the plugin's `version.php` metadata from `2026101402` / release `1.2.0` to `2026101501` / release `1.2.2`.

### Scope

The Git comparison includes the intervening `1.2.1` tag but changes only `pix/icon.png` and `version.php`. No functional source changes are present between the published `1.2.0` and `1.2.2` releases.

---

These notes describe observed differences between released tags; they do not claim runtime deployment or test execution. Later commits, if any, are excluded.
