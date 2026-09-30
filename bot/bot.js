const fs = require('node:fs');
const path = require('node:path');
const { Client, GatewayIntentBits, Events } = require('discord.js');

require('dotenv').config({ path: path.join(__dirname, '..', '.env') });

const token = process.env.DISCORD_BOT_TOKEN;
if (!token) {
  console.error('DISCORD_BOT_TOKEN is not set in .env');
  process.exit(1);
}

const verboseFile = path.join(__dirname, '..', 'verbose.on');

const isVerbose = () => fs.existsSync(verboseFile);

function setVerbose(on) {
  if (on) {
    fs.writeFileSync(verboseFile, '1');
  } else if (fs.existsSync(verboseFile)) {
    fs.unlinkSync(verboseFile);
  }
}

const client = new Client({
  intents: [
    GatewayIntentBits.Guilds,
    GatewayIntentBits.GuildMessages,
    GatewayIntentBits.MessageContent,
  ],
});

client.once(Events.ClientReady, (c) => {
  console.log(`Logged in as ${c.user.tag}`);
});

client.on(Events.MessageCreate, async (message) => {
  if (message.author.bot) return;

  const content = message.content.trim();

  if (/^[!/]ping$/i.test(content)) {
    await message.reply('pong');
    return;
  }

  const match = content.match(/^[!/]verbose(?:\s+(on|off|status))?$/i);
  if (!match) {
    if (/^[!/]help$/i.test(content)) {
      await message.reply('Commands: `!verbose`, `!verbose on`, `!verbose off`, `!verbose status`');
    }
    return;
  }

  const arg = (match[1] || '').toLowerCase();
  if (arg === 'on') {
    setVerbose(true);
  } else if (arg === 'off') {
    setVerbose(false);
  } else if (arg !== 'status') {
    setVerbose(!isVerbose());
  }

  await message.reply(`verbose is now **${isVerbose() ? 'ON' : 'OFF'}**`);
});

client.login(token).catch((error) => {
  console.error('Failed to log in:', error.message);
  process.exit(1);
});
