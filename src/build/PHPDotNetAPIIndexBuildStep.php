<?hh // strict

namespace HHVM\UserDocumentation;

final class PHPDotNetAPIIndexBuildStep extends BuildStep {
  use CodegenBuildStep;

  public function buildAll(): void {
    Log::i("\nPHPDotNetAPIIndex");

    $code = $this->writeCode(
      'PHPDotNetAPIIndex.hhi',
      $this->getIndexData(),
    );
    file_put_contents(
      BuildPaths::PHP_DOT_NET_API_INDEX,
      $code,
    );
  }


  private function getIndexData(): array<string, PHPDotNetAPIIndexEntry> {
    $reader = new PHPDocsIndexReader(
      file_get_contents(BuildPaths::PHP_DOT_NET_INDEX_JSON)
    );
    $defs = $reader->getAllAPIDefinitions();

    $out = [];
    foreach ($defs as $name => $id) {
      $type = explode('.', $id)[0];
      $type = APIDefinitionType::coerce($type);
      if ($type === null) {
        continue;
      }

      $url = sprintf('http://php.net/manual/en/%s.php', $id);

      $supported =
        $type === APIDefinitionType::FUNCTION_DEF
        ? function_exists($name)
        : (
            class_exists($name)
            || trait_exists($name)
            || interface_exists($name)
          );

      $out[$name] = shape(
        'type' => $type,
        'url' => $url,
        'supportedInHHVM' => $supported,
      );
    }

    ksort($out);

    return $out;
  }
}
