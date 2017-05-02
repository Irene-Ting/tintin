<?php
namespace Tintin\Lexique;

trait CompileExtends
{
    use CompileStacks;

    /**
     * @param $expression
     * @return mixed
     */
    protected function compileExtendsStack($expression)
    {
        foreach (['Include', 'Block', 'EndBlock', 'Extends', 'Inject'] as $token) {
            $out = $this->{'compile'.$token}($expression);
            if (strlen($out) !== 0) {
                $expression = $out;
            }
        }
        return $expression;
    }

    /**
     * @param $expression
     * @return string
     */
    protected function compileBlock($expression)
    {
        $output = preg_replace_callback("/\n*\%block\s*\((.+?)(?:,(.+?))?\)\n*/m", function ($match) {
            array_shift($match);
            $content = 'null';
            if (count($match) == 2) {
                $content = $match[1];
            }
            return "<?php \$__tintin->startStack('{$match[0]}', $content); ?>";
        }, $expression);

        return $output == $expression ? '' : $output;
    }

    /**
     * @param $expression
     * @return string
     */
    protected function compileEndBlock($expression)
    {
        $output = preg_replace_callback("/\n*%endblock\n*/m", function () {
            return "<?php \$__tintin->endStack(); ?>";
        }, $expression);

        return $output == $expression ? '' : $output;
    }

    /**
     * @param $expression
     * @return string
     */
    protected function compileInclude($expression)
    {
        $base_r = "/\%include\s*\(((?:\n|\s|\t)*(?:.+?)(?:\n|\s|\t)*)\)/sm";
        $output = preg_replace_callback($base_r, function ($match) {
            return "<?php \$__tintin->include({$match[1]}, ['__tintin' => \$__tintin]); ?>";
        }, $expression);

        return $output == $expression ? '' : $output;
    }

    /**
     * @param $expression
     * @return int|string
     */
    protected function compileExtends($expression)
    {
        $base_r = "/\%extends\s*\(((?:\n|\s|\t)*(?:.+?)(?:\n|\s|\t)*)\)/sm";
        $output = preg_replace_callback($base_r, function ($match) {
            return "<?php \$__tintin->include({$match[1]}, ['__tintin' => \$__tintin]); ?>";
        }, $expression);
        return $output == $expression ? '' : $output;
    }

    /**
     * Permet de faire l'injection de contenu d'un block
     *
     * @param string $expression
     * @return mixed|null
     */
    protected function compileInject($expression)
    {
        $base_r = "/\%inject\s*\(((?:\n|\s|\t)*(?:.+?)(?:\n|\s|\t)*)\)/sm";

        $output = preg_replace_callback($base_r, function ($match) {
            return "<?php \$__tintin->getStack('{$match[1]}'); ?>";
        }, $expression);

        return $output == $expression ? '' : $output;
    }
}