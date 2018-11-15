<?hh

namespace Ytake\Hhttp;

use type Facebook\Experimental\Http\Message\ResponseInterface;

class Response implements ResponseInterface {
  use MessageTrait;

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

  private ?string $reason = '';

  public function __construct(
    private StatusCode $status = StatusCode::Ok,
    Map<string, varray<string>> $headers = Map{},
    mixed $body = null,
    private string $protocol = '1.1',
    ?string $reason = null
  ) {
    if ('' !== $body && null !== $body) {
      $this->stream = $this->getStream($body);
    }
    $this->setHeaders($headers);
    $this->reason = $reason;
    if (null === $reason && $this->phrases->contains($status)) {
      $this->reason = $this->phrases->at($status);
    }
  }

  public function getStatusCode() {
    return $this->status;
  }

  public function getReasonPhrase() {
    return $this->reason;
  }

  public function withStatus($code, $reasonPhrase = '') {
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
}
