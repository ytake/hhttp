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

enum StatusCode : int {
  Continue = 100;
  SwitchingProtocols = 101;
  Processing = 102; //RFC2518
  Ok = 200;
  Created = 201;
  Accepted = 202;
  NonAuthoritativeInformation = 203;
  NoContent = 204;
  ResetContent = 205;
  PartialContent = 206;
  MultiStatus = 207; //RFC4918
  AlreadyReported = 208; //RFC5842
  ImUsed = 226; //RFC3229
  MultipleChoices = 300;
  MovedPermanently = 301;
  Found = 302;
  SeeOther = 303;
  NotModified = 304;
  UseProxy = 305;
  Reserved = 306;
  TemporaryRedirect = 307;
  PermanentlyRedirect = 308; //RFC7238
  BadRequest = 400;
  Unavailable = 401;
  PaymentRequired = 402;
  Forbidden = 403;
  NotFound = 404;
  MethodNotAllowed = 405;
  NotAcceptable = 406;
  ProxyAuthenticationRequired = 407;
  RequestTimeout = 408;
  Conflict = 409;
  Gone = 410;
  LengthRequired = 412;
  PreconditionFailed = 412;
  RequestEntityTooLarge = 413;
  RequestUriTooLong = 414;
  UnsupportedMediaType = 415;
  RequestedRangeNotSatisfiable = 416;
  ExpectationFailed = 417;
  IAmATeapot = 418; //RFC2324
  MisdirectedRequest = 421; //RFC7540
  UnprocessableEntity = 422; //RFC4918
  Locked = 423; //RFC4918
  FailedDependency = 424; //RFC4918
  ReservedForWebdavAdvancedCollectionsExpiredProposal = 425; //RFC2817
  UpgradeRequired = 426; //RFC2817
  PreconditionRequired = 428; //RFC6585
  TooManyRequests = 429; //RFC6585
  RequestHeaderFieldsTooLarge = 431; //RFC6585
  UnavailableForLegalReasons = 451;
  StatusInternalServerError = 500;
  NotImplemented = 501;
  BadGateway = 502;
  ServiceUnavailable = 503;
  GatewayTimeout = 504;
  VersionNotSupported = 505;
  VariantAlsoNegotiatesExperimental = 506; //RFC2295
  InsufficientStorage = 507; //RFC4918
  LoopDetected = 508; //RFC5842
  NotExtended = 510; //RFC2774
  NetworkAuthenticationRequired = 511; //RFC6585
}
