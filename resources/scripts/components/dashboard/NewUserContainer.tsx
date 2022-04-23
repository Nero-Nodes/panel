import React, { useState } from 'react';
import { useStoreState } from 'easy-peasy';
import Button from '@/components/elements/Button';
import tw from 'twin.macro';
import PageContentBlock from '@/components/elements/PageContentBlock';

const NewUserContainer = () => {
    const [ isSubmit, setSubmit ] = useState(false);
    const user = useStoreState(state => state.user.data!);

    const submit = () => {
        setSubmit(false);
        // @ts-ignore
        window.location = '/store/servers/new';
    };

    return (
        <PageContentBlock>
            <div css={tw`flex justify-center`}>
                <div css={tw`w-full sm:w-3/4 md:w-1/2 p-12 md:p-20 bg-neutral-100 rounded-lg shadow-lg text-center relative`}>
                    <h2 css={tw`mb-10 mt-2 text-neutral-900 font-bold text-4xl`}>
                        <span role={'img'} aria-label={'hello'} css={tw`mr-1`}>👋</span>Welcome, {user.username}!
                    </h2>
                    <p css={tw`text-sm text-neutral-700`}>
                           Looks like it is your first time using Nero. Let&apos;s get you started
                           by deploying a server.
                    </p>
                    <p css={tw`text-sm text-neutral-700 mt-4`}>
                          Head over to the Store page and create a server with us, using the resources
                          that have automatically been assigned to your account. Once that has been done,
                          you&apos;ll be redirected to the dashboard where your server will be active.
                    </p>
                    <Button
                        css={tw`mt-10`}
                        onClick={submit}
                        disabled={isSubmit}
                        size={'xlarge'}
                    >
                        Get Started
                    </Button>
                </div>
            </div>
        </PageContentBlock>
    );
};

export default NewUserContainer;
