import 'dotenv/config';
import crypto from 'node:crypto';
import { existsSync } from 'node:fs';
import path from 'node:path';
import { fileURLToPath } from 'node:url';
import express from 'express';
import helmet from 'helmet';
import { ChannelType, Client, GatewayIntentBits } from 'discord.js';

const requiredEnv = ['DISCORD_BOT_TOKEN', 'DISCORD_STAFF_CHANNEL_ID', 'INTERNAL_BOT_API_TOKEN'];
for (const key of requiredEnv) {
  if (!process.env[key]) {
    console.error(`${key} is required`);
    process.exit(1);
  }
}

const client = new Client({
  intents: [GatewayIntentBits.Guilds],
});

const app = express();
app.use(helmet());
app.use(express.json({ limit: '32kb' }));

const reconnectDelayMs = Number(process.env.DISCORD_RECONNECT_DELAY_MS || 15000);
const currentDir = path.dirname(fileURLToPath(import.meta.url));
const embedIconAttachmentName = 'minevida-logo.png';
const embedIconAttachmentUrl = `attachment://${embedIconAttachmentName}`;
const configuredEmbedIconPath = process.env.DISCORD_EMBED_ICON_PATH || '../public/images/MineVidaLogo.png';
const embedIconPath = path.isAbsolute(configuredEmbedIconPath)
  ? configuredEmbedIconPath
  : path.resolve(currentDir, configuredEmbedIconPath);
let reconnecting = false;

function safeEquals(a, b) {
  const left = Buffer.from(a || '');
  const right = Buffer.from(b || '');

  if (left.length !== right.length) {
    return false;
  }

  return crypto.timingSafeEqual(left, right);
}

function requireInternalToken(req, res, next) {
  const header = req.get('authorization') || '';
  const token = header.startsWith('Bearer ') ? header.slice(7) : '';

  if (!safeEquals(token, process.env.INTERNAL_BOT_API_TOKEN)) {
    return res.status(401).json({ success: false, error: 'Unauthorized' });
  }

  return next();
}

function assertPayload(condition, message) {
  if (!condition) {
    const error = new Error(message);
    error.status = 422;
    throw error;
  }
}

function assertBotReady() {
  if (!client.isReady()) {
    const error = new Error('El bot de Discord aun no esta listo. Intenta de nuevo en unos segundos.');
    error.status = 503;
    throw error;
  }
}

function cleanContent(content) {
  return String(content || '').trim().slice(0, 1900);
}

function embedUsesLocalIcon(embed) {
  return [
    embed?.author?.icon_url,
    embed?.footer?.icon_url,
    embed?.thumbnail?.url,
    embed?.image?.url,
  ].includes(embedIconAttachmentUrl);
}

function cleanMessageOptions(body) {
  const content = cleanContent(body.content);
  const embeds = Array.isArray(body.embeds) ? body.embeds.slice(0, 10) : [];
  const components = Array.isArray(body.components) ? body.components.slice(0, 5) : [];
  const options = {};

  if (content.length > 0) {
    options.content = content;
  }

  if (embeds.length > 0) {
    options.embeds = embeds;
  }

  if (components.length > 0) {
    options.components = components;
  }

  if (embeds.some(embedUsesLocalIcon)) {
    if (existsSync(embedIconPath)) {
      options.files = [{ attachment: embedIconPath, name: embedIconAttachmentName }];
    } else {
      console.warn(`Embed icon file not found: ${embedIconPath}`);
    }
  }

  assertPayload(Boolean(options.content) || embeds.length > 0, 'content o embeds es requerido');

  return options;
}

app.get('/health', (_req, res) => {
  res.json({ success: true, ready: client.isReady() });
});

app.post('/send-dm', requireInternalToken, async (req, res, next) => {
  try {
    assertBotReady();

    const discordId = String(req.body.discord_id || '').trim();
    const messageOptions = cleanMessageOptions(req.body);

    assertPayload(/^\d{16,25}$/.test(discordId), 'discord_id invalido');

    const user = await client.users.fetch(discordId);
    await user.send(messageOptions);

    return res.json({
      success: true,
      application_id: req.body.application_id ?? null,
      type: req.body.type ?? 'dm',
    });
  } catch (error) {
    if (error.code === 50007) {
      return res.json({ success: false, error: 'El usuario tiene los mensajes privados cerrados.' });
    }

    return next(error);
  }
});

app.post('/send-staff-channel-message', requireInternalToken, async (req, res, next) => {
  try {
    assertBotReady();

    const messageOptions = cleanMessageOptions(req.body);

    const channel = await client.channels.fetch(process.env.DISCORD_STAFF_CHANNEL_ID);
    assertPayload(channel && channel.isTextBased() && channel.type !== ChannelType.DM, 'Canal de staff invalido');

    await channel.send(messageOptions);

    return res.json({
      success: true,
      application_id: req.body.application_id ?? null,
      type: 'staff_channel',
    });
  } catch (error) {
    return next(error);
  }
});

app.post('/send-channel-message', requireInternalToken, async (req, res, next) => {
  try {
    assertBotReady();

    const channelId = String(req.body.channel_id || '').trim();
    const messageOptions = cleanMessageOptions(req.body);

    assertPayload(/^\d{16,25}$/.test(channelId), 'channel_id invalido');

    const channel = await client.channels.fetch(channelId);
    assertPayload(channel && channel.isTextBased() && channel.type !== ChannelType.DM, 'Canal de Discord invalido');

    await channel.send(messageOptions);

    return res.json({
      success: true,
      channel_id: channelId,
      type: 'channel_message',
    });
  } catch (error) {
    return next(error);
  }
});

app.use((error, _req, res, _next) => {
  const status = Number(error.status || 500);
  const message = status >= 500 ? 'Error interno del bot.' : error.message;

  if (status >= 500) {
    console.error(error.message);
  }

  res.status(status).json({ success: false, error: message });
});

client.on('clientReady', () => {
  console.log(`Lumoryx bot ready as ${client.user.tag}`);
});

client.on('error', (error) => {
  console.error('Discord client error:', error.message);
});

client.on('shardError', (error) => {
  console.error('Discord shard error:', error.message);
});

client.on('warn', (message) => {
  console.warn('Discord warning:', message);
});

client.on('disconnect', () => {
  console.warn('Discord gateway disconnected.');
});

async function reconnectDiscord(reason) {
  if (reconnecting) {
    return;
  }

  reconnecting = true;
  console.error(`Discord connection crashed: ${reason}`);
  console.error(`Trying to reconnect in ${Math.round(reconnectDelayMs / 1000)}s...`);

  setTimeout(async () => {
    try {
      client.destroy();
      await client.login(process.env.DISCORD_BOT_TOKEN);
      console.log('Discord reconnect requested.');
    } catch (error) {
      console.error('Discord reconnect failed:', error.message);
      reconnecting = false;
      reconnectDiscord(error.message);
      return;
    }

    reconnecting = false;
  }, reconnectDelayMs);
}

process.on('uncaughtException', (error) => {
  if (String(error.message || '').includes('Opening handshake has timed out')) {
    reconnectDiscord(error.message);
    return;
  }

  console.error('Uncaught exception:', error);
  process.exitCode = 1;
});

process.on('unhandledRejection', (reason) => {
  const message = reason instanceof Error ? reason.message : String(reason);

  if (message.includes('Opening handshake has timed out')) {
    reconnectDiscord(message);
    return;
  }

  console.error('Unhandled rejection:', reason);
});

async function loginDiscord() {
  try {
    await client.login(process.env.DISCORD_BOT_TOKEN);
  } catch (error) {
    console.error('Discord login failed:', error.message);
    reconnectDiscord(error.message);
  }
}

const host = process.env.BOT_HOST || '127.0.0.1';
const port = Number(process.env.BOT_PORT || 3001);

app.listen(port, host, () => {
  console.log(`Lumoryx internal bot API listening on http://${host}:${port}`);
});

await loginDiscord();
