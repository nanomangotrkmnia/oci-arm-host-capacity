const fs = require('node:fs');
const path = require('node:path');
const { Client, GatewayIntentBits, Events, PermissionFlagsBits } = require('discord.js');

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

async function clearBotMessages(channel, botId) {
  let deleted = 0;
  let before;
  const cutoff = Date.now() - 14 * 24 * 60 * 60 * 1000;

  while (true) {
    const fetched = await channel.messages.fetch({ limit: 100, before });
    if (fetched.size === 0) break;
    before = fetched.last().id;

    const botMsgs = [...fetched.values()].filter((m) => m.author.id === botId);
    if (botMsgs.length > 0) {
      const recent = botMsgs.filter((m) => m.createdTimestamp > cutoff);
      const old = botMsgs.filter((m) => m.createdTimestamp <= cutoff);

      if (recent.length > 1) {
        await channel.bulkDelete(recent, true).catch(() => {});
      } else if (recent.length === 1) {
        await recent[0].delete().catch(() => {});
      }
      for (const m of old) {
        await m.delete().catch(() => {});
      }
      deleted += botMsgs.length;
    }

    if (fetched.size < 100) break;
  }

  return deleted;
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

  if (/^[!/]clearlogs$/i.test(content)) {
    const canManage = message.member
      && (message.member.permissions.has(PermissionFlagsBits.ManageMessages)
        || message.member.permissions.has(PermissionFlagsBits.Administrator));

    if (!canManage) {
      await message.reply('You need the Manage Messages permission to do that.');
      return;
    }

    const deleted = await clearBotMessages(message.channel, client.user.id);
    await message.delete().catch(() => {});
    const reply = await message.channel.send(`Cleared ${deleted} of my message(s).`);
    setTimeout(() => reply.delete().catch(() => {}), 5000);
    return;
  }

  const match = content.match(/^[!/]verbose(?:\s+(on|off|status))?$/i);
  if (!match) {
    if (/^[!/]help$/i.test(content)) {
      await message.reply('Commands: `!verbose [on|off|status]`, `!clearlogs`, `!ping`');
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
