<?php

namespace App\Entity;

use App\Contract\UploadInterface;
use App\Repository\ArticleRepository;
use DateTime;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\HttpFoundation\File\UploadedFile;
use Symfony\Component\Validator\Constraints as Assert;
use Gedmo\Mapping\Annotation as Gedmo;


#[ORM\Entity(repositoryClass: ArticleRepository::class)]
#[ORM\HasLifecycleCallbacks()]
#[Gedmo\Loggable()]
class Article implements UploadInterface
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column(type: 'integer')]
    private int $id;

    #[ORM\Column(type: 'string', length: 128)]
    #[Assert\NotBlank(message: 'Le titre ne peut être vide')]
    #[Assert\Length(
        min:10, max: 20,
        minMessage: 'Le titre ne peut faire moins de {{ limit }} caractères',
        maxMessage: 'Le titre ne peut faire plus de {{ limit }} caractères'
    )
    ]
    #[Gedmo\Versioned]
    private string $title;

    #[ORM\Column(type: 'text')]
    #[Assert\Length(
        min: 8,
        minMessage: 'Le texte ne peut faire moins de {{ limit }} caractères'
    )]
    #[Gedmo\Versioned]
    private string $content;

    #[ORM\Column(type: 'datetime')]
    private DateTime $createdAt;

    #[ORM\Column(type: 'datetime', nullable: true)]
    private ?DateTime $updatedAt;

    #[ORM\ManyToOne(targetEntity: Author::class, inversedBy: 'articles')]
    private Author $author;

    #[ORM\ManyToOne(targetEntity: Category::class, inversedBy: 'articles')]
    private Category $category;

    #[ORM\OneToMany(mappedBy: 'article', targetEntity: Comment::class)]
    #[Assert\Count(max: 3, maxMessage: 'Pas plus de {{ limit }} commentaires par article')]
    private Collection $comments;

    #[ORM\Column(type: 'string', length: 255, nullable: true)]
    private ?string $imageFileName = null;

    #[Assert\Image()]
    private ?UploadedFile $uploadedFile = null;

    #[ORM\Column(type:'string', length: 255, unique:true)]
    #[Gedmo\Slug(fields: ['title'])]
    private $slug;

    #[ORM\ManyToMany(
        targetEntity: Tag::class,
        inversedBy: 'articles',
        cascade: ['persist'])]
    private Collection $tags;

    public function __construct()
    {
        $this->comments = new ArrayCollection();
        $this->tags = new ArrayCollection();
    }

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getTitle(): ?string
    {
        return $this->title;
    }

    public function setTitle(string $title): self
    {
        $this->title = $title;

        return $this;
    }

    public function getContent(): ?string
    {
        return $this->content;
    }

    public function setContent(string $content): self
    {
        $this->content = $content;

        return $this;
    }

    public function getCreatedAt(): ?\DateTimeInterface
    {
        return $this->createdAt;
    }

    public function setCreatedAt(\DateTimeInterface $createdAt): self
    {
        $this->createdAt = $createdAt;

        return $this;
    }

    public function getUpdatedAt(): ?\DateTimeInterface
    {
        return $this->updatedAt;
    }

    public function setUpdatedAt(?\DateTimeInterface $updatedAt): self
    {
        $this->updatedAt = $updatedAt;

        return $this;
    }

    public function getAuthor(): ?Author
    {
        return $this->author;
    }

    public function setAuthor(?Author $author): self
    {
        $this->author = $author;

        return $this;
    }

    public function getCategory(): ?Category
    {
        return $this->category;
    }

    public function setCategory(?Category $category): self
    {
        $this->category = $category;

        return $this;
    }

    /**
     * @param int $numberOfWords
     * @return string
     */
    public function getExcerpt(int $numberOfWords): string{
        $tab = array_slice(
            explode(' ', $this->content),
            0,
            $numberOfWords
        );

        return implode(' ', $tab);
    }

    /**
     * @return Collection<int, Comment>
     */
    public function getComments(): Collection
    {
        return $this->comments;
    }

    public function addComment(Comment $comment): self
    {
        if (!$this->comments->contains($comment)) {
            $this->comments[] = $comment;
            $comment->setArticle($this);
        }

        return $this;
    }

    public function removeComment(Comment $comment): self
    {
        if ($this->comments->removeElement($comment)) {
            // set the owning side to null (unless already changed)
            if ($comment->getArticle() === $this) {
                $comment->setArticle(null);
            }
        }

        return $this;
    }

    /**
     * @return UploadedFile
     */
    public function getUploadedFile(): ?UploadedFile
    {
        return $this->uploadedFile;
    }

    /**
     * @param UploadedFile $uploadedFile
     * @return Article
     */
    public function setUploadedFile(UploadedFile $uploadedFile): Article
    {
        $this->uploadedFile = $uploadedFile;

        return $this;
    }





    #[ORM\PrePersist()]
    public function prePersistEvent(): void{
        if(empty($this->createdAt))
        $this->createdAt = new DateTime();
    }

    #[ORM\PreUpdate()]
    public function preUpdateEvent(): void{
        $this->updatedAt = new DateTime();
    }

    public function getImageFileName(): ?string
    {
        return $this->imageFileName;
    }

    public function setImageFileName(?string $imageFileName): self
    {
        $this->imageFileName = $imageFileName;

        return $this;
    }

    public function hasImage(): bool{
        return $this->imageFileName !== null;
    }

    /**
     * @return Collection<int, Tag>
     */
    public function getTags(): Collection
    {
        return $this->tags;
    }

    public function addTag(Tag $tag): self
    {
        if (!$this->tags->contains($tag)) {
            $this->tags[] = $tag;
        }

        return $this;
    }

    public function removeTag(Tag $tag): self
    {
        $this->tags->removeElement($tag);

        return $this;
    }

    public function getSlug(): ?string
    {
        return $this->slug;
    }

    public function setSlug(string $slug): self
    {
        $this->slug = $slug;

        return $this;
    }
}
