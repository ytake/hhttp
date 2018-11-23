<?hh // strict

namespace Ytake\Hungrr;

use type Facebook\Experimental\Http\Message\ResponseInterface;

use namespace HH\Lib\Experimental\IO;

class Response implements ResponseInterface {
  use MessageTrait, IOTrait;

  private ImmMap<StatusCode, string> $phrases = ImmMap{
    StatusCode::CONTINUE => 'Continue',
    StatusCode::SWITCHING_PROTOCOLS => 'Switching Protocols',
    StatusCode::PROCESSING => 'Processing',
    StatusCode::OK => 'OK',
    StatusCode::CREATED => 'Created',
    StatusCode::ACCEPTED => 'Accepted',
    StatusCode::NON_AUTHORITATIVE_INFORMATION => 'Non-Authoritative Information',
    StatusCode::NO_CONTENT => 'No Content',
    StatusCode::RESET_CONTENT => 'Reset Content',
    StatusCode::PARTIAL_CONTENT => 'Partial Content',
    StatusCode::MULTI_STATUS => 'Multi-status',
    StatusCode::ALREADY_REPORTED => 'Already Reported',
    StatusCode::IM_USED => 'IM Used',
    StatusCode::MULTIPLE_CHOICES => 'Multiple Choices',
    StatusCode::MOVED_PERMANENTLY => 'Moved Permanently',
    StatusCode::FOUND => 'Found',
    StatusCode::SEE_OTHER => 'See Other',
    StatusCode::NOT_MODIFIED => 'Not Modified',
    StatusCode::USE_PROXY => 'Use Proxy',
    StatusCode::SWITCH_PROXY => 'Switch Proxy',
    StatusCode::TEMPORARY_REDIRECT  => 'Temporary Redirect',
    StatusCode::PERMANENTLY_REDIRECT => 'Permanently Redirect',
    StatusCode::BAD_REQUEST => 'Bad Request',
    StatusCode::UNAVAILABLE => 'Unauthorized',
    StatusCode::PAYMENT_REQUIRED => 'Payment Required',
    StatusCode::FORBIDDEN => 'Forbidden',
    StatusCode::NOT_FOUND => 'Not Found',
    StatusCode::METHOD_NOT_ALLOWED => 'Method Not Allowed',
    StatusCode::NOT_ACCEPTABLE => 'Not Acceptable',
    StatusCode::PROXY_AUTHENTICATION_REQUIRED => 'Proxy Authentication Required',
    StatusCode::REQUEST_TIMEOUT => 'Request Time-out',
    StatusCode::CONFLICT => 'Conflict',
    StatusCode::GONE => 'Gone',
    StatusCode::LENGTH_REQUIRED => 'Length Required',
    StatusCode::PRECONDITION_FAILED => 'Precondition Failed',
    StatusCode::REQUEST_ENTITY_TOO_LARGE => 'Request Entity Too Large',
    StatusCode::REQUEST_URI_TOO_LONG => 'Request-URI Too Large',
    StatusCode::UNSUPPORTED_MEDIA_TYPE => 'Unsupported Media Type',
    StatusCode::REQUESTED_RANGE_NOT_SATISFIABLE  => 'Requested range not satisfiable',
    StatusCode::EXPECTATION_FAILED => 'Expectation Failed',
    StatusCode::I_AM_A_TEAPOT => 'I\'m a teapot',
    StatusCode::MISDIRECTED_REQUEST => 'Misdirected Request',
    StatusCode::UNPROCESSABLE_ENTITY => 'Unprocessable Entity',
    StatusCode::LOCKED => 'Locked',
    StatusCode::FAILED_DEPENDENCY => 'Failed Dependency',
    StatusCode::UNORDERED_COLLECTION => 'Unordered Collection',
    StatusCode::UPGRADE_REQUIRED => 'Upgrade Required',
    StatusCode::PRECONDITION_REQUIRED => 'Precondition Required',
    StatusCode::TOO_MANY_REQUESTS => 'Too Many Requests',
    StatusCode::REQUEST_HEADER_FIELDS_TOO_LARGE => 'Request Header Fields Too Large',
    StatusCode::UNAVAILABLE_FOR_LEGAL_REASONS => 'Unavailable For Legal Reasons',
    StatusCode::INTERNAL_SERVER_ERROR => 'Internal Server Error',
    StatusCode::NOT_IMPLEMENTED => 'Not Implemented',
    StatusCode::BAD_GATEWAY => 'Bad Gateway',
    StatusCode::SERVICE_UNAVAILABLE => 'Service Unavailable',
    StatusCode::GATEWAY_TIMEOUT => 'Gateway Time-out',
    StatusCode::VERSION_NOT_SUPPORTED => 'HTTP Version not supported',
    StatusCode::VARIANT_ALSO_NEGOTIATES_EXPERIMENTAL => 'Variant Also Negotiates',
    StatusCode::INSUFFICIENT_STORAGE => 'Insufficient Storage',
    StatusCode::LOOP_DETECTED => 'Loop Detected',
    StatusCode::NOT_EXTENDED => 'Not Extended',
    StatusCode::NETWORK_AUTHENTICATION_REQUIRED => 'Network Authentication Required',
  };

  public function __construct(
    private StatusCode $status = StatusCode::OK,
    dict<string, vec<string>> $headers = dict[],
    string $body = '',
    private string $protocol = '1.1',
    protected string $reason = ''
  ) {
    $this->setHeaders($headers);
    $this->createIO();
    $this->getBody()->rawWriteBlocking($body);
    if ($this->phrases->contains($status)) {
      $this->reason = $this->phrases->at($status);
    }
    if ($reason !== '') {
      $this->reason = $reason;
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
