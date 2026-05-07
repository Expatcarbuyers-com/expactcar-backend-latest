<?php

namespace App\Filament\Resources\Blogs\Schemas;

use App\Models\BlogCategory;
use Filament\Forms\Components\DateTimePicker;
use Filament\Forms\Components\FileUpload;
use Filament\Forms\Components\RichEditor;
use Filament\Schemas\Components\Section;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use Filament\Schemas\Schema;
use Illuminate\Support\Str;

class BlogForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                Section::make('Content')
                    ->schema([
                        TextInput::make('title')
                            ->required()
                            ->maxLength(255)
                            ->live(onBlur: true)
                            ->afterStateUpdated(fn ($state, callable $set) =>
                                $set('slug', Str::slug($state))
                            )
                            ->columnSpanFull(),

                        TextInput::make('slug')
                            ->required()
                            ->unique(ignoreRecord: true)
                            ->maxLength(255)
                            ->helperText('Auto-generated from title. Edit to customise the URL.')
                            ->columnSpanFull(),

                        Textarea::make('excerpt')
                            ->rows(2)
                            ->maxLength(500)
                            ->columnSpanFull(),

                        RichEditor::make('content')
                            ->required()
                            ->toolbarButtons([
                                'bold', 'italic', 'underline', 'strike',
                                'h2', 'h3',
                                'bulletList', 'orderedList',
                                'blockquote', 'codeBlock',
                                'attachFiles',
                                'link', 'redo', 'undo',
                            ])
                            ->fileAttachmentsDisk('public')
                            ->fileAttachmentsDirectory('blog-content')
                            ->fileAttachmentsVisibility('public')
                            ->columnSpanFull(),
                    ]),

                Section::make('Meta & Publishing')
                    ->schema([
                        Select::make('category_id')
                            ->label('Category')
                            ->options(BlogCategory::orderBy('name')->pluck('name', 'id'))
                            ->searchable()
                            ->native(false)
                            ->createOptionForm([
                                TextInput::make('name')->required(),
                                TextInput::make('slug')->required(),
                            ])
                            ->createOptionUsing(fn (array $data) =>
                                BlogCategory::create($data)->id
                            ),

                        DateTimePicker::make('published_at')
                            ->label('Publish At')
                            ->helperText('Leave empty to save as draft.'),

                        FileUpload::make('cover_image')
                            ->label('Cover Image')
                            ->image()
                            ->directory('blog-covers')
                            ->imageEditor(),

                        TextInput::make('meta_title')->maxLength(70),

                        Textarea::make('meta_description')->rows(2)->maxLength(160),
                    ])->columns(2),
            ]);
    }
}
