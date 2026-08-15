// Comprehensive Web API polyfills for Phusion Passenger environment
const util = require("util");
if (typeof globalThis.TextEncoder === "undefined") globalThis.TextEncoder = util.TextEncoder;
if (typeof globalThis.TextDecoder === "undefined") globalThis.TextDecoder = util.TextDecoder;

try {
  const streamWeb = require("stream/web");
  if (typeof globalThis.ReadableStream === "undefined") globalThis.ReadableStream = streamWeb.ReadableStream;
  if (typeof globalThis.WritableStream === "undefined") globalThis.WritableStream = streamWeb.WritableStream;
  if (typeof globalThis.TransformStream === "undefined") globalThis.TransformStream = streamWeb.TransformStream;
} catch (e) {}

try {
  const crypto = require("crypto");
  if (typeof globalThis.crypto === "undefined" && crypto.webcrypto) {
    globalThis.crypto = crypto.webcrypto;
  }
} catch (e) {}

if (typeof globalThis.Request === "undefined" || typeof globalThis.fetch === "undefined") {
  try {
    const undici = require("undici");
    globalThis.Request = globalThis.Request || undici.Request;
    globalThis.Response = globalThis.Response || undici.Response;
    globalThis.Headers = globalThis.Headers || undici.Headers;
    globalThis.fetch = globalThis.fetch || undici.fetch;
    globalThis.FormData = globalThis.FormData || undici.FormData;
    globalThis.File = globalThis.File || undici.File;
    globalThis.Blob = globalThis.Blob || undici.Blob;
  } catch (e) {
    try {
      const undici = require("next/dist/compiled/undici");
      globalThis.Request = globalThis.Request || undici.Request;
      globalThis.Response = globalThis.Response || undici.Response;
      globalThis.Headers = globalThis.Headers || undici.Headers;
      globalThis.fetch = globalThis.fetch || undici.fetch;
      globalThis.FormData = globalThis.FormData || undici.FormData;
      globalThis.File = globalThis.File || undici.File;
      globalThis.Blob = globalThis.Blob || undici.Blob;
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
const appDir = __dirname;

const logFile = path.join(appDir, "server_error.log");
function logError(msg) {
  try {
    fs.appendFileSync(logFile, `[${new Date().toISOString()}] ${msg}\n`);
  } catch (e) {}
}

logError(`\n=== Server starting with Node ${process.version} at ${appDir} ===`);

const app = next({ dev, dir: appDir });
const handle = app.getRequestHandler();

let prepareError = null;
let isPrepared = false;

const preparePromise = app.prepare()
  .then(() => {
    isPrepared = true;
    logError("Next.js app prepared successfully! Ready to serve requests.");
  })
  .catch((err) => {
    prepareError = err;
    logError("Next.js prepare failed: " + (err.stack || err.message));
  });

const server = http.createServer(async (req, res) => {
  try {
    if (prepareError) {
      logError("Handling request while prepare failed: " + prepareError.message);
      res.statusCode = 500;
      res.setHeader("Content-Type", "text/plain");
      return res.end("Server Build Initialization Error: " + prepareError.message);
    }

    if (!isPrepared) {
      await preparePromise;
      if (prepareError) {
        res.statusCode = 500;
        res.setHeader("Content-Type", "text/plain");
        return res.end("Server Build Initialization Error: " + prepareError.message);
      }
    }

    const parsedUrl = parse(req.url, true);
    await handle(req, res, parsedUrl);
  } catch (err) {
    logError("Request Handler Error: " + (err.stack || err.message));
    res.statusCode = 500;
    res.setHeader("Content-Type", "text/plain");
    res.end("Internal Server Error: " + (err.message || "Unknown error"));
  }
});

if (typeof PhusionPassenger !== "undefined") {
  server.listen("passenger");
} else {
  server.listen(port, () => {
    logError(`Next.js listening on port ${port}`);
  });
}
