const http = require("http");
const { parse } = require("url");
const next = require("next");

const dev = process.env.NODE_ENV !== "production";
const port = process.env.PORT || 3000;

// Explicitly set directory to __dirname for cPanel Passenger
const app = next({ dev, dir: __dirname });
const handle = app.getRequestHandler();

app.prepare()
  .then(() => {
    const server = http.createServer((req, res) => {
      try {
        const parsedUrl = parse(req.url, true);
        handle(req, res, parsedUrl);
      } catch (err) {
        console.error("Error handling request:", req.url, err);
        res.statusCode = 500;
        res.end("Internal Server Error");
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
    console.error("Next.js prepare failed:", err);
    process.exit(1);
  });
