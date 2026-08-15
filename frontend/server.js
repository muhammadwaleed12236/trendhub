const http = require("http");
const { parse } = require("url");
const next = require("next");
const fs = require("fs");
const path = require("path");

const dev = false;
const port = process.env.PORT || 3000;

const logFile = path.join(__dirname, "server_error.log");
function logError(msg) {
  try {
    fs.appendFileSync(logFile, `[${new Date().toISOString()}] ${msg}\n`);
  } catch (e) {}
}

// Initialize Next.js app
const app = next({ dev, dir: __dirname });
const handle = app.getRequestHandler();

let isPrepared = false;
const preparePromise = app.prepare()
  .then(() => {
    isPrepared = true;
    logError("Next.js app prepared successfully");
  })
  .catch((err) => {
    logError("Next.js prepare failed: " + (err.stack || err.message));
  });

// Create HTTP server that listens IMMEDIATELY so Passenger doesn't timeout
const server = http.createServer(async (req, res) => {
  try {
    if (!isPrepared) {
      await preparePromise;
    }
    const parsedUrl = parse(req.url, true);
    await handle(req, res, parsedUrl);
  } catch (err) {
    logError("Request Error: " + (err.stack || err.message));
    res.statusCode = 500;
    res.end("Internal Server Error: " + (err.message || "Unknown"));
  }
});

// Bind to Passenger socket immediately
if (typeof PhusionPassenger !== "undefined") {
  server.listen("passenger");
} else {
  server.listen(port, () => {
    logError(`Next.js listening on port ${port}`);
  });
}
