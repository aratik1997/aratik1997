const fs = require("fs");
const path = require("path");

// Widely-circulated quotes from classical Muslim scholars on knowledge and work.
const QUOTES = [
  { text: "Knowledge is not what is memorized; knowledge is what benefits.", author: "Imam Ash-Shafi'i" },
  { text: "Knowledge is better than wealth. Knowledge protects you; you have to protect wealth.", author: "Ali ibn Abi Talib" },
  { text: "The purpose of knowledge is action, not the accumulation of more knowledge.", author: "Imam Al-Ghazali" },
  { text: "The seeker of knowledge is in need of hard striving, sincere effort, and much devotion.", author: "Ibn Khaldun" },
  { text: "Seek knowledge, and along with it acquire tranquillity and dignity.", author: "Umar ibn Al-Khattab" },
];

const readmePath = path.join(__dirname, "..", "..", "README.md");
const readme = fs.readFileSync(readmePath, "utf8");

const startMarker = "<!-- QUOTE:START -->";
const endMarker = "<!-- QUOTE:END -->";
const startIdx = readme.indexOf(startMarker);
const endIdx = readme.indexOf(endMarker);

if (startIdx === -1 || endIdx === -1 || endIdx < startIdx) {
  console.error("Quote markers not found in README.md");
  process.exit(1);
}

// Deterministic daily rotation: same quote all day, changes once every 24h.
const dayOfYear = Math.floor(
  (Date.now() - new Date(new Date().getFullYear(), 0, 0)) / 86400000
);
const quote = QUOTES[dayOfYear % QUOTES.length];

const block = `${startMarker}\n        <h3><i>"${quote.text}"</i></h3>\n        <p><b>— ${quote.author}</b></p>\n        ${endMarker}`;

const before = readme.slice(0, startIdx);
const after = readme.slice(endIdx + endMarker.length);
const updated = `${before}${block}${after}`;

fs.writeFileSync(readmePath, updated);
