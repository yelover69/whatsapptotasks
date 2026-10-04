# WhatsApp AI Task Bot

Turn WhatsApp messages into Google Tasks with Gemini Flash. 100% free, zero Docker, zero subscriptions.

## Stack
- **AI**: Gemini 1.5/2.0 Flash (free 1.5k req/day)
- **Database**: Google Tasks API (free 50k req/day)
- **WhatsApp**: Meta Cloud API (free 1k conv/mo)
- **Host**: Vercel Serverless or Render

## Structure
```
api/                     -> Vercel serverless entry points
src/
  GeminiClassifier.php   -> AI message classifier & date resolver
  GoogleTasksClient.php  -> OAuth token refresh + Tasks API
  MetaWhatsAppClient.php -> Webhook handshake & event parser
  MessageFilter.php      -> Phone & group whitelist engine
config.php               -> Env config
webhook.php              -> Core webhook runner
setup_google_auth.php    -> 1-click Google OAuth setup
test_cli.php             -> Terminal tester
```

## Quick Test
```bash
export GEMINI_API_KEY="your-key"
php test_cli.php "Dentist tomorrow 3pm"
```

Read `DEPLOYMENT_GUIDE.md` to deploy live in ~5 minutes.
