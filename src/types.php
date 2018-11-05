<?hh // strict

namespace Ytrake\Hhttp;

type ParsedUrlShape = shape(
  ?'schema' => string,
  ?'host' => string,
  ?'port' => ?int,
  ?'user' => string,
  ?'pass' => string,
  ?'path' => string,
  ?'query' => string,
  ?'fragment' => string
);
