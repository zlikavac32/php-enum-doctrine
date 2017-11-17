<?php

declare(strict_types=1);

namespace Zlikavac32\DoctrineEnum\Tests\Fixtures;

/**
 * @Entity
 * @Table(name="answer")
 */
class AnswerEntity
{
    /**
     * @Id
     * @Column(name="id", type="integer")
     * @GeneratedValue(strategy="AUTO")
     * @var int
     */
    private $id;

    /**
     * @Column(type="enum_yes_no", nullable=true)
     * @var YesNoEnum|null
     */
    private $answer;

    /**
     * @return int
     */
    public function getId(): int
    {
        return $this->id;
    }

    /**
     * @return YesNoEnum|null
     */
    public function getAnswer(): ?YesNoEnum
    {
        return $this->answer;
    }

    /**
     * @param YesNoEnum|null $answer
     *
     * @return AnswerEntity
     */
    public function setAnswer(?YesNoEnum $answer): self
    {
        $this->answer = $answer;

        return $this;
    }
}
