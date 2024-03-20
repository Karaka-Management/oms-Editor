<?php
/**
 * Jingga
 *
 * PHP Version 8.2
 *
 * @package   Modules\Editor\Models
 * @copyright Dennis Eichhorn
 * @license   OMS License 2.0
 * @version   1.0.0
 * @link      https://jingga.app
 */
declare(strict_types=1);

namespace Modules\Editor\Models;

/**
 * EditorDoc class.
 *
 * @package Modules\Editor\Models
 * @license OMS License 2.0
 * @link    https://jingga.app
 * @since   1.0.0
 *
 * @property \Modules\Editor\Models\EditorDoc[] $notes
 */
trait EditorDocListTrait
{
    /**
     * EditorDocs.
     *
     * @var EditorDoc[]
     * @since 1.0.0
     */
    public array $notes = [];

    /**
     * Get notes by type
     *
     * @param int $type EditorDoc type
     *
     * @return EditorDoc
     *
     * @since 1.0.0
     */
    public function getEditorDocByType(int $type) : EditorDoc
    {
        foreach ($this->notes as $note) {
            if ($note->type?->id === $type) {
                return $note;
            }
        }

        return new NullEditorDoc();
    }

    /**
     * Get all notess by type name
     *
     * @param string $type EditorDoc type
     *
     * @return EditorDoc
     *
     * @since 1.0.0
     */
    public function getEditorDocByTypeName(string $type) : EditorDoc
    {
        foreach ($this->notes as $note) {
            if ($note->type?->title === $type) {
                return $note;
            }
        }

        return new NullEditorDoc();
    }

    /**
     * Get all notess by type name
     *
     * @param string $type EditorDoc type
     *
     * @return EditorDoc[]
     *
     * @since 1.0.0
     */
    public function getEditorDocsByTypeName(string $type) : array
    {
        $notes = [];
        foreach ($this->notes as $note) {
            if ($note->type?->title === $type) {
                $notes[] = $note;
            }
        }

        return $notes;
    }

    /**
     * Check if file with a certain type name exists
     *
     * @param string $type Type name
     *
     * @return bool
     *
     * @since 1.0.0
     */
    public function hasEditorDocTypeName(string $type) : bool
    {
        foreach ($this->notes as $note) {
            if ($note->type?->title === $type) {
                return true;
            }
        }

        return false;
    }
}
