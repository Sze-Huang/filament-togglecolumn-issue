<?php

namespace App\Filament\Resources;

use App\Filament\Resources\UserResource\Pages;
use App\Filament\Resources\UserResource\RelationManagers;
use App\Models\User;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Notifications\Notification;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\SoftDeletingScope;
use Illuminate\Validation\ValidationException;

class UserResource extends Resource
{
    protected static ?string $model = User::class;

    protected static ?string $navigationIcon = 'heroicon-o-rectangle-stack';

    public static function form( Form $form ): Form
    {
        return $form
            ->schema( [
                Forms\Components\TextInput::make( 'name' )
                    ->required(),
                Forms\Components\TextInput::make( 'email' )
                    ->email()
                    ->required(),
                Forms\Components\DateTimePicker::make( 'email_verified_at' ),
                Forms\Components\Toggle::make( 'status' )
                    ->required(),
            ] );
    }

    public static function table( Table $table ): Table
    {
        return $table
            ->columns( [
                Tables\Columns\TextColumn::make( 'name' )
                    ->searchable(),
                Tables\Columns\TextColumn::make( 'email' )
                    ->searchable(),
                Tables\Columns\TextColumn::make( 'email_verified_at' )
                    ->dateTime()
                    ->sortable(),
                Tables\Columns\ToggleColumn::make( 'status' )
                    ->label( 'Status' )
                    ->onIcon( 'heroicon-o-check' )
                    ->offIcon( 'heroicon-o-x-mark' )
                    ->onColor( 'success' )
                    ->offColor( 'danger' )
                    ->updateStateUsing( function ($record, $state): bool {
                        $user = auth()->user();

                        if (str_ends_with( $user->email, '@admin.com' )) {
                            $record->update( ['status' => $state] );

                            Notification::make()
                                ->title( 'Done' )
                                ->color( 'success' )
                                ->success()
                                ->send();

                            return $state; // Confirm toggle on success
                        } else {
                            throw ValidationException::withMessages( [] );

                        }

                    } ),
                Tables\Columns\TextColumn::make( 'created_at' )
                    ->dateTime()
                    ->sortable()
                    ->toggleable( isToggledHiddenByDefault: true ),
                Tables\Columns\TextColumn::make( 'updated_at' )
                    ->dateTime()
                    ->sortable()
                    ->toggleable( isToggledHiddenByDefault: true ),
            ] )
            ->filters( [
                //
            ] )
            ->actions( [
                Tables\Actions\EditAction::make(),
            ] )
            ->bulkActions( [
                Tables\Actions\BulkActionGroup::make( [
                    Tables\Actions\DeleteBulkAction::make(),
                ] ),
            ] );
    }

    public static function getRelations(): array
    {
        return [
            //
        ];
    }

    public static function getPages(): array
    {
        return [
            'index'  => Pages\ListUsers::route( '/' ),
            'create' => Pages\CreateUser::route( '/create' ),
            'edit'   => Pages\EditUser::route( '/{record}/edit' ),
        ];
    }
}
