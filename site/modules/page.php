<?php

class Page {
    private string $template;

    public function __construct(string $template) {
        // Загружаем шаблон из файла
        if (!file_exists($template)) {
            die("Файл шаблона не найден: $template");
        }

        $this->template = file_get_contents($template);
    }

    public function Render(array $data): void {
        $output = $this->template;

        // Подставляем значения
        foreach ($data as $key => $value) {
            $output = str_replace('{{ ' . $key . ' }}', htmlspecialchars($value), $output);
        }

        echo $output;
    }
}
