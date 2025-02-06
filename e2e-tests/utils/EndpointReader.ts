import * as fs from 'fs';
import * as path from "path";

export class EndpointReader {
  endpoints: Array<string>;

  constructor() {
    this.read();
  }

  read() {
    const excludes = fs.readFileSync(path.join(__dirname, '..', 'endpoints', 'exclude_endpoints.csv'), 'utf8').split('\n');
    this.endpoints = fs.readFileSync(path.join(__dirname, '..', 'endpoints', 'endpoints.csv'), 'utf8')
      .split('\n')
      .filter(line => !excludes.includes(line))
      .map(line => line.replace(/index.*/, ''));

    return this.endpoints;
  }

  filter(prefix: string) {
    return this.endpoints.filter(line => line.match(new RegExp(`^${ prefix }`)));
  }
}
