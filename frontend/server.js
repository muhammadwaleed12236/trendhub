// Polyfill web standards for older Node versions or Passenger environments
if (typeof globalThis.Request === "undefined") {
  try {
    const { Request, Response, Headers, fetch, FormData } = require("undici");
    globalThis.Request = globalThis.Request || Request;
    globalThis.Response = globalThis.Response || Response;
    globalThis.Headers = globalThis.Headers || Headers;
    globalThis.fetch = globalThis.fetch || fetch;
    globalThis.FormData = globalThis.FormData || FormData;
  } catch (e) {
    try {
      const undici = require("next/dist/compiled/undici");
      globalThis.Request = globalThis.Request || undici.Request;
      globalThis.Response = globalThis.Response || undici.Response;
      globalThis.Headers = globalThis.Headers || undici.Headers;
      globalThis.fetch = globalThis.fetch || undici.fetch;
      globalThis.FormData = globalThis.FormData || undici.FormData;
    } catch (e2) {}
  }
}

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

logError(`Starting Next.js server with Node ${process.version}`);

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

// Create HTTP server that listens immediately for Phusion Passenger
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
    res.end("Internal Server Error: " + (err.message || "Unknown error"));
  }
});

// Bind to Passenger socket
if (typeof PhusionPassenger !== "undefined") {
  server.listen("passenger");
} else {
  server.listen(port, () => {
    logError(`Next.js listening on port ${port}`);
  });
}
