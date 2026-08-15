const http = require("http");
const { parse } = require("url");
const next = require("next");
const fs = require("fs");
const path = require("path");

const dev = process.env.NODE_ENV !== "production";
const port = process.env.PORT || 3000;

const logFile = path.join(__dirname, "server_error.log");
function logError(msg) {
  try {
    fs.appendFileSync(logFile, `[${new Date().toISOString()}] ${msg}\n`);
  } catch (e) {}
}

const app = next({ dev, dir: __dirname });
const handle = app.getRequestHandler();

app.prepare()
  .then(() => {
    const server = http.createServer(async (req, res) => {
      try {
        const parsedUrl = parse(req.url, true);
        await handle(req, res, parsedUrl);
      } catch (err) {
        logError("Request Error: " + (err.stack || err.message));
        res.statusCode = 500;
        res.end("Internal Server Error: " + (err.message || "Unknown error"));
      }
    });

    if (typeof PhusionPassenger !== "undefined") {
      server.listen("passenger");
    } else {
      server.listen(port, () => {
        console.log(`> Next.js ready on port ${port}`);
      });
    }
  })
  .catch((err) => {
    logError("Next.js prepare failed: " + (err.stack || err.message));
    console.error("Next.js prepare failed:", err);
    process.exit(1);
  });
