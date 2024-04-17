<?php

interface Command {
    public function execute();
    public function undo();
}

// Memento para encapsular o estado do editor de texto
class TextEditorMemento {
    private $text;

    public function __construct($text) {
        $this->text = $text;
    }

    public function getText() {
        return $this->text;
    }
}

// Receiver
class TextEditor {
    private $text = '';

    public function insertText($text, $position) {
        $this->text = substr_replace($this->text, $text, $position, 0);
    }

    public function deleteText($position, $length) {
        $this->text = substr_replace($this->text, '', $position, $length);
    }

    public function createMemento() {
        return new TextEditorMemento($this->text);
    }

    public function restoreFromMemento(TextEditorMemento $memento) {
        $this->text = $memento->getText();
    }

    public function getText() {
        return $this->text;
    }
}

// ConcreteCommand para inserção de texto
class InsertTextCommand implements Command {
    private $receiver;
    private $text;
    private $position;

    public function __construct(TextEditor $receiver, $text, $position) {
        $this->receiver = $receiver;
        $this->text = $text;
        $this->position = $position;
    }

    public function execute() {
        $this->receiver->insertText($this->text, $this->position);
    }

    public function undo() {
        $this->receiver->deleteText($this->position, strlen($this->text));
    }
}

// ConcreteCommand para exclusão de texto
class DeleteTextCommand implements Command {
    private $receiver;
    private $position;
    private $deletedText;

    public function __construct(TextEditor $receiver, $position, $length) {
        $this->receiver = $receiver;
        $this->position = $position;
        $this->deletedText = substr($receiver->getText(), $position, $length);
    }

    public function execute() {
        $this->receiver->deleteText($this->position, strlen($this->deletedText));
    }

    public function undo() {
        $this->receiver->insertText($this->deletedText, $this->position);
    }
}

// Invoker com injeção de dependência
class TextEditorInvoker {
    private $undoStack = [];

    public function execute(Command $command) {
        $command->execute();
        $this->undoStack[] = $command;
    }

    public function undo() {
        if (!empty($this->undoStack)) {
            $command = array_pop($this->undoStack);
            $command->undo();
        }
    }
}

// Exemplo de uso
$textEditor = new TextEditor();
$invoker = new TextEditorInvoker();

// Inserir texto "Hello, " na posição 0
$invoker->execute(new InsertTextCommand($textEditor, 'Hello, ', 0));

// Inserir texto "world!" na posição 7
$invoker->execute(new InsertTextCommand($textEditor, 'Roberto Ancelmo!', 7));

echo "Texto resultante: " . $textEditor->getText() . "\n<br />";

// Excluir o texto "Roberto Ancelmo!" a partir da posição 7
$invoker->execute(new DeleteTextCommand($textEditor, 7, strlen('Roberto Ancelmo!')));

echo "Texto após desfazer: " . $textEditor->getText() . "\n<br />";

// Desfazer a exclusão do texto "Roberto Ancelmo!"
$invoker->undo();

echo "Texto após refazer: " . $textEditor->getText() . "\n<br />";
