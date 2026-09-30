# Moodle local_ai_bridge

`local_ai_bridge` is a tenant-aware AI orchestration layer for Moodle. It centralizes purposes, access control, credits,
routing and usage statistics while keeping all provider-specific behavior inside provider subplugins.

## Architecture

The parent plugin defines the subplugin type `aibridge`. Bundled providers are installed under `bridge/<name>`:

- `aibridge_openai` — OpenAI Responses API and Chat Completions API;
- `aibridge_gemini` — Google Gemini `generateContent` API;
- `aibridge_claude` — Anthropic Claude Messages API;
- `aibridge_ollama` — self-hosted Ollama `/api/chat` API.

The parent **does not contain provider-specific request logic**. Each bridge subplugin owns its configuration form,
validation, authentication headers, request payload, HTTP call, response parsing, token accounting and provider cost
calculation. New providers implement `local_ai_bridge\local\bridge\provider_interface` and can be added without changing
the parent router.

Both legacy `plugintypes` and modern `subplugintypes` declarations are included in `db/subplugins.json` so the bundled
subplugins are discoverable across the supported Moodle versions and by the validation tooling.

## Tenant isolation

Tenants are matched using the standard Moodle user profile fields `institution`, `department`, or the combination of
both. Automatic tenant creation can be enabled, but is disabled by default.

A delegated tenant administrator can manage only assigned tenants. Site administrators/managers
with `local/ai_bridge:manageall` can manage every tenant.

## Purposes, roles and routes

A purpose represents a deployment scenario such as `course-assistant`, `feedback`, or `content-draft`. It contains the
system instruction, generation parameters and the number of internal credits charged for a successful request.

Each tenant has logical AI roles. New tenants receive `student` and `teacher`, with `student` as the default. Routes
connect:

`tenant + purpose + logical role -> connection + model`

Routes have priorities. The API tries routes in order and can fall back to another provider when the preferred route
fails. A route without a role acts as a tenant-wide fallback.

## Credits

Credits are an internal quota and are intentionally separate from provider billing. A purpose can charge 0, 1, 5 or any
decimal number of credits per successful request, while each provider subplugin independently reports an estimated
monetary cost based on token prices configured in that connection.

This separation avoids coupling tenant quotas to provider pricing changes. Tenant administrators can also set individual
user credit limits.

## Security

Provider configuration is encrypted with Moodle `core\encryption` before it is stored. Prompts and model responses are
not persisted by the parent plugin.

Custom provider URLs are checked against the global endpoint allowlist. Exact hosts are allowed by default, wildcard
subdomains require an explicit `*.example.org` entry, and non-standard ports must be listed explicitly, such
as `ollama.internal:11434`. This is especially important for self-hosted providers such as Ollama and
Anthropic-compatible gateways because unrestricted tenant-configurable endpoints could otherwise be used for SSRF. Add
every internal Ollama endpoint that tenants are allowed to call under:

`Site administration -> Plugins -> Local plugins -> AI Bridge -> Allowed provider hosts`

## Calling AI Bridge from another plugin

```php
$response = \local_ai_bridge\api::generate(
    'course-assistant',
    [
        ['role' => 'user', 'content' => 'Explain this concept using a practical example.'],
    ]
);

echo $response->text;
```

The API resolves the current user tenant, checks tenant/user state and credits, resolves purpose and logical role, tries
configured routes, delegates the request to the selected `aibridge_*` provider, records usage metadata and debits
credits only after success.

## Privacy

Usage statistics store user ID, provider, model, token counts, credits, estimated cost, latency and status. The plugin
deliberately does not store prompt text or generated text. A Moodle Privacy API provider implements metadata, export,
deletion and user-list deletion.

## Compatibility

- Moodle 4.5+
- PHP version required by the target Moodle branch

## Validation

The repository includes CI that runs `EduardoKrausME/moodle-plugin-validate` and a PHP syntax pass on every push and
pull request.

```bash
php /path/to/moodle-plugin-validate/bin/moodle-string-validate /path/to/local_ai_bridge
```

## License

GNU GPL v3 or later.
