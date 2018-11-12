<?hh // strict

namespace Ytake\Hhttp;

type ParsedUrlShape = shape(
  ?'scheme' => string,
  ?'host' => string,
  ?'port' => ?int,
  ?'user' => string,
  ?'pass' => string,
  ?'path' => string,
  ?'query' => string,
  ?'fragment' => string
);

enum HttpMethod : string {
  HEAD = 'HEAD';
  GET = 'GET';
  POST = 'POST';
  PATCH = 'PATCH';
  PUT = 'PUT';
  DELETE = 'DELETE';
}
