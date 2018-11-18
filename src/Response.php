<?hh // strict

namespace Ytake\Hhttp;

use type Facebook\Experimental\Http\Message\ResponseInterface;

use namespace HH\Lib\Experimental\IO;

class Response implements ResponseInterface {
  use MessageTrait, IOTrait;

  private ImmMap<StatusCode, string> $phrases = ImmMap{
    StatusCode::Continue => 'Continue',
    StatusCode::SwitchingProtocols => 'Switching Protocols',
    StatusCode::Processing => 'Processing',
    StatusCode::Ok => 'OK',
    StatusCode::Created => 'Created',
    StatusCode::Accepted => 'Accepted',
    StatusCode::NonAuthoritativeInformation => 'Non-Authoritative Information',
    StatusCode::NoContent => 'No Content',
    StatusCode::ResetContent => 'Reset Content',
    StatusCode::PartialContent => 'Partial Content',
    StatusCode::MultiStatus => 'Multi-status',
    StatusCode::AlreadyReported => 'Already Reported',
    StatusCode::ImUsed => 'IM Used',
    StatusCode::MultipleChoices => 'Multiple Choices',
    StatusCode::MovedPermanently => 'Moved Permanently',
    StatusCode::Found => 'Found',
    StatusCode::SeeOther => 'See Other',
    StatusCode::NotModified => 'Not Modified',
    StatusCode::UseProxy => 'Use Proxy',
    StatusCode::Reserved => 'Switch Proxy',
    StatusCode::TemporaryRedirect  => 'Temporary Redirect',
    StatusCode::PermanentlyRedirect => 'Permanently Redirect',
    StatusCode::BadRequest => 'Bad Request',
    StatusCode::Unavailable => 'Unauthorized',
    StatusCode::PaymentRequired => 'Payment Required',
    StatusCode::Forbidden => 'Forbidden',
    StatusCode::NotFound => 'Not Found',
    StatusCode::MethodNotAllowed => 'Method Not Allowed',
    StatusCode::NotAcceptable => 'Not Acceptable',
    StatusCode::ProxyAuthenticationRequired => 'Proxy Authentication Required',
    StatusCode::RequestTimeout => 'Request Time-out',
    StatusCode::Conflict => 'Conflict',
    StatusCode::Gone => 'Gone',
    StatusCode::LengthRequired => 'Length Required',
    StatusCode::PreconditionFailed => 'Precondition Failed',
    StatusCode::RequestEntityTooLarge => 'Request Entity Too Large',
    StatusCode::RequestUriTooLong => 'Request-URI Too Large',
    StatusCode::UnsupportedMediaType => 'Unsupported Media Type',
    StatusCode::RequestedRangeNotSatisfiable  => 'Requested range not satisfiable',
    StatusCode::ExpectationFailed => 'Expectation Failed',
    StatusCode::IAmATeapot => 'I\'m a teapot',
    StatusCode::MisdirectedRequest => 'Misdirected Request',
    StatusCode::UnprocessableEntity => 'Unprocessable Entity',
    StatusCode::Locked => 'Locked',
    StatusCode::FailedDependency => 'Failed Dependency',
    StatusCode::ReservedForWebdavAdvancedCollectionsExpiredProposal => 'Unordered Collection',
    StatusCode::UpgradeRequired => 'Upgrade Required',
    StatusCode::PreconditionRequired => 'Precondition Required',
    StatusCode::TooManyRequests => 'Too Many Requests',
    StatusCode::RequestHeaderFieldsTooLarge => 'Request Header Fields Too Large',
    StatusCode::UnavailableForLegalReasons => 'Unavailable For Legal Reasons',
    StatusCode::StatusInternalServerError => 'Internal Server Error',
    StatusCode::NotImplemented => 'Not Implemented',
    StatusCode::BadGateway => 'Bad Gateway',
    StatusCode::ServiceUnavailable => 'Service Unavailable',
    StatusCode::GatewayTimeout => 'Gateway Time-out',
    StatusCode::VersionNotSupported => 'HTTP Version not supported',
    StatusCode::VariantAlsoNegotiatesExperimental => 'Variant Also Negotiates',
    StatusCode::InsufficientStorage => 'Insufficient Storage',
    StatusCode::LoopDetected => 'Loop Detected',
    StatusCode::NotExtended => 'Not Extended',
    StatusCode::NetworkAuthenticationRequired => 'Network Authentication Required',
  };

  public function __construct(
    private StatusCode $status = StatusCode::Ok,
    Map<string, vec<string>> $headers = Map{},
    string $body = '',
    private string $protocol = '1.1',
    protected string $reason = ''
  ) {
    $this->setHeaders($headers);
    $this->createIO();
    $this->getBody()->rawWriteBlocking($body);
    $this->reason = $reason;
    if ($this->phrases->contains($status)) {
      $this->reason = $this->phrases->at($status);
    }
  }

  public function getStatusCode(): int {
    return $this->status;
  }

  public function getReasonPhrase(): string {
    return $this->reason;
  }

  public function withStatus(int $code, string $reasonPhrase = ''): this {
    if (!$code is int && !$code is string) {
      throw new \InvalidArgumentException('Status code has to be an integer');
    }
    $new = clone $this;
    $new->status = StatusCode::assert((int) $code);
    if ((null === $reasonPhrase || '' === $reasonPhrase) && $this->phrases->contains($new->status)) {
      $reasonPhrase = $this->phrases->at($new->status);
    }
    $new->reason = $reasonPhrase;
    return $new;
  }

  public function getBody(): IO\WriteHandle {
    $wh = $this->writeHandle;
    invariant($wh is IO\WriteHandle, "handler error.");
    return $wh;
  }

  public function withBody(IO\WriteHandle $body): this {
    $new = clone $this;
    $new->writeHandle = $body;
    return $new;
  }

  public function readBody(): IO\ReadHandle {
    $rh = $this->readHandle;
    invariant($rh is IO\ReadHandle, "handler error.");
    return $rh;
  }
}
